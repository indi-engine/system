<?php
require_once __DIR__ . '/../vendor/autoload.php';
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class Indi {

    /**
     * An internal static variable, will be used to store data, that should be accessible anywhere
     *
     * @var array
     */
    protected static $_registry = array();

    /**
     * An internal array, containing info related to what kind of suspicious events should be logged.
     * Currently is has two types of such events: 'jerror' and 'mflush'. Logging of 'jerror' events
     * is turned On (e.g is boolean `true`) by default, as if such an event occurs, this mean that
     * there is something wrong with php-code, logic or smth, and this have to be investigated by developer.
     * Logging of 'mflush' events is turned Off (e.g is boolean `false`) by default, because in most cases
     * such events means that user is trying to assign an incorrect values for an some entry's fields,
     * and he is informed about that by the UI of Indi Engine, so he has the ability to check/fix incorrect
     * values and try to save the entry again. However, sometimes it is useful to turn it 'On'. Currently
     * such an approach will be useful if there is some background operations, performed by Cron, etc,
     * so, in those cases problems happened but nobody see it, and logging for them is the only way to be informed
     *
     * @var array
     */
    protected static $_logging = array(
        'jerror' => true,
        'jflush' => false,
        'mflush' => false
    );

    /**
     * jflush-redirect. If not empty, all jflush() calls will be logged despite Indi::logging('flush') may be `false`,
     * and additionally there would be a redirect to url, specified by Indi::$jfr
     *
     * NOTE: *_Row->mflush() calls also involve jflush() call
     *
     * @var string
     */
    public static $jfr = '';

    /**
     * An internal static variable, will be used to store data, got from `staticblock` table 
	 * as an assotiative array  and that should be accessible anywhere
     *
     * @var array|null
     */
    protected static $_blockA = null;

    /**
     * Compilation template
     *
     * @var string
     */
    public static $cmpTpl = '';

    /**
     * Compilation result/output
     *
     * @var string
     */
    public static $cmpOut = array();

    /**
     * Array of prompt answers
     *
     * @var array
     */
    public static $answer = array();

    /**
     * @var null
     */
    protected static $_ws = null;

    /**
     * @var null
     */
    protected static $_mq = null;

    /**
     * Regular expressions patterns for common usage
     *
     * @var array
     */
    protected static $_rex = array(
        'email' => '/^([a-zA-Z0-9_\-\.]+)@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.)|(([a-zA-Z0-9\-]+\.)+))([a-zA-Z]{2,6}|[0-9]{1,3})(\]?)$/',
        'date' => '/^[0-9]{4}\-[0-9]{2}\-[0-9]{2}$/',
        'zerodate' => '/^[0\.\-\/ ]*$/',
        'year' => '/^[0-9]{4}$/',
        'hrgb' => '/^[0-9]{3}#([0-9a-fA-F]{6})$/',
        'rgb' => '/^#([0-9a-fA-F]{6})$/',
        'htmleventattr' => 'on[a-zA-Z]+\s*=\s*"[^"]+"',
        'php' => '/<\?/',
        'phpsplit' => '/(<\?|\?>)/',
        'int11' => '/^(-?[1-9][0-9]{0,9}|0)$/',
        'int11lz' => '/^-?[0-9]{1,10}$/',
        'int11list' => '/^[1-9][0-9]{0,9}(,[1-9][0-9]{0,9})*$/',
        'bool' => '/^(0|1)$/',
        'time' => '/^[0-9]{2}:[0-9]{2}:[0-9]{2}$/',
        'double72' => '/^([1-9][0-9]{0,6}|[0-9])(\.[0-9]{1,2})?$/',
        'decimal112' => '/^(-?([1-9][0-9]{1,7}|[0-9]))(\.[0-9]{1,2})?$/',
        'decimal143' => '/^(-?([1-9][0-9]{1,9}|[0-9]))(\.[0-9]{1,3})?$/',
        'datetime' => '/^[0-9]{4}\-[0-9]{2}\-[0-9]{2} [0-9]{2}:[0-9]{2}:[0-9]{2}$/',
        'url' => '/^(ht|f)tp(s?)\:\/\/(([a-zA-Z0-9\-\._]+(\.[a-zA-Z0-9\-\._]+)+)|localhost)(\/?)([a-zA-Z0-9\-\.\?\,\'\/\\\+&amp;%\$#_]*)?([\d\w\.\/\%\+\-\=\&amp;\?\:\\\&quot;\'\,\|\~\;]*)$/',
        'urichunk' => '',
        'varchar255' => '/^([[:print:]]{0,255})$/',
        'dir' => ':^([A-Z][\:])?/.*/$:',
        'grs' => '/^[a-zA-Z0-9]{15}$/',
        'phone' => '/^\+7 \([0-9]{3}\) [0-9]{3}-[0-9]{2}-[0-9]{2}$/',
        'vk' => '~^https://vk.com/([a-zA-Z0-9_\.]{3,})~',
        'coords' => '/^([0-9]{1,3}+\.[0-9]{1,12})\s*,\s*([0-9]{1,3}+\.[0-9]{1,12}+)$/',
        'timespan' => '/^[0-9]{2}:[0-9]{2}-[0-9]{2}:[0-9]{2}$/',
        'ipv4' => '~^(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)$~',
        'base64' => '^~(?:[A-Za-z0-9+/]{4})*(?:[A-Za-z0-9+/]{2}==|[A-Za-z0-9+/]{3}=)?$~',
        'wskey' => '~^[A-Za-z0-9+/]{22}==$~',
        'ctx' => '~^i-[a-zA-Z\-0-9]+$~',
        'json' => '/
          (?(DEFINE)
             (?<number>   -? (?= [1-9]|0(?!\d) ) \d+ (\.\d+)? ([eE] [+-]? \d+)? )
             (?<boolean>   true | false | null )
             (?<string>    " ([^"\n\r\t\\\\]* | \\\\ ["\\\\bfnrt\/] | \\\\ u [0-9a-f]{4} )* " )
             (?<array>     \[  (?:  (?&json)  (?: , (?&json)  )*  )?  \s* \] )
             (?<pair>      \s* (?&string) \s* : (?&json)  )
             (?<object>    \{  (?:  (?&pair)  (?: , (?&pair)  )*  )?  \s* \} )
             (?<json>   \s* (?: (?&number) | (?&boolean) | (?&string) | (?&array) | (?&object) ) \s* )
          )
          \A (?&json) \Z
          /six'
    );

    /**
     * Mime types dictionary
     *
     * @var array
     */
    protected static $_mime = array(

        'definitive' => array (
            'application/x-authorware-bin' => 'aab',
            'application/x-authorware-map' => 'aam',
            'application/x-authorware-seg' => 'aas',
            'text/vnd.abc' => 'abc',
            'video/animaflex' => 'afl',
            'application/x-aim' => 'aim',
            'text/x-audiosoft-intra' => 'aip',
            'application/x-navi-animation' => 'ani',
            'application/x-nokia-9000-communicator-add-on-software' => 'aos',
            'application/mime' => 'aps',
            'application/arj' => 'arj',
            'image/x-jg' => 'art',
            'text/asp' => 'asp',
            'application/x-mplayer2' => 'asx',
            'video/x-ms-asf-plugin' => 'asx',
            'audio/x-au' => 'au',
            'application/x-troff-msvideo' => 'avi',
            'video/avi' => 'avi',
            'video/msvideo' => 'avi',
            'video/x-msvideo' => 'avi',
            'video/avs-video' => 'avs',
            'application/x-bcpio' => 'bcpio',
            'application/mac-binary' => 'bin',
            'application/macbinary' => 'bin',
            'application/x-binary' => 'bin',
            'application/x-macbinary' => 'bin',
            'image/x-windows-bmp' => 'bmp',
            'application/x-bzip' => 'bz',
            'application/vnd.ms-pki.seccat' => 'cat',
            'application/clariscad' => 'ccad',
            'application/x-cocoa' => 'cco',
            'application/cdf' => 'cdf',
            'application/x-cdf' => 'cdf',
            'application/java' => 'class',
            'application/java-byte-code' => 'class',
            'application/x-java-class' => 'class',
            'application/x-cpio' => 'cpio',
            'application/mac-compactpro' => 'cpt',
            'application/x-compactpro' => 'cpt',
            'application/x-cpt' => 'cpt',
            'application/pkcs-crl' => 'crl',
            'application/pkix-crl' => 'crl',
            'application/x-x509-user-cert' => 'crt',
            'application/x-csh' => 'csh',
            'text/x-script.csh' => 'csh',
            'application/x-pointplus' => 'css',
            'text/css' => 'css',
            'application/x-deepv' => 'deepv',
            'video/dl' => 'dl',
            'video/x-dl' => 'dl',
            'application/commonground' => 'dp',
            'application/drafting' => 'drw',
            'application/x-dvi' => 'dvi',
            'drawing/x-dwf (old)' => 'dwf',
            'model/vnd.dwf' => 'dwf',
            'application/acad' => 'dwg',
            'application/dxf' => 'dxf',
            'text/x-script.elisp' => 'el',
            'application/x-bytecode.elisp (compiled elisp)' => 'elc',
            'application/x-elc' => 'elc',
            'application/x-esrehber' => 'es',
            'text/x-setext' => 'etx',
            'application/envoy' => 'evy',
            'application/vnd.fdf' => 'fdf',
            'application/fractals' => 'fif',
            'image/fif' => 'fif',
            'video/fli' => 'fli',
            'video/x-fli' => 'fli',
            'text/vnd.fmi.flexstor' => 'flx',
            'video/x-atomic3d-feature' => 'fmf',
            'image/vnd.fpx' => 'fpx',
            'image/vnd.net-fpx' => 'fpx',
            'application/freeloader' => 'frl',
            'image/g3fax' => 'g3',
            'image/gif' => 'gif',
            'video/gl' => 'gl',
            'video/x-gl' => 'gl',
            'application/x-gsp' => 'gsp',
            'application/x-gss' => 'gss',
            'application/x-gtar' => 'gtar',
            'multipart/x-gzip' => 'gzip',
            'application/x-hdf' => 'hdf',
            'text/x-script' => 'hlb',
            'application/hlp' => 'hlp',
            'application/x-winhelp' => 'hlp',
            'application/binhex' => 'hqx',
            'application/binhex4' => 'hqx',
            'application/mac-binhex' => 'hqx',
            'application/mac-binhex40' => 'hqx',
            'application/x-binhex40' => 'hqx',
            'application/x-mac-binhex40' => 'hqx',
            'application/hta' => 'hta',
            'text/x-component' => 'htc',
            'text/webviewhtml' => 'htt',
            'x-conference/x-cooltalk' => 'ice ',
            'image/x-icon' => 'ico',
            'application/x-ima' => 'ima',
            'application/x-httpd-imap' => 'imap',
            'application/inf' => 'inf ',
            'application/x-internett-signup' => 'ins',
            'application/x-ip2' => 'ip ',
            'video/x-isvideo' => 'isu',
            'audio/it' => 'it',
            'application/x-inventor' => 'iv',
            'i-world/i-vrml' => 'ivr',
            'application/x-livescreen' => 'ivy',
            'audio/x-jam' => 'jam ',
            'application/x-java-commerce' => 'jcm',
            'image/x-jps' => 'jps',
            'application/x-javascript' => 'js',
            'image/jutvision' => 'jut',
            'music/x-karaoke' => 'kar',
            'application/x-ksh' => 'ksh',
            'text/x-script.ksh' => 'ksh',
            'audio/x-liveaudio' => 'lam',
            'application/lha' => 'lha',
            'application/x-lha' => 'lha',
            'application/x-lisp' => 'lsp',
            'text/x-script.lisp' => 'lsp',
            'text/x-la-asf' => 'lsx',
            'application/x-lzh' => 'lzh',
            'application/lzx' => 'lzx',
            'application/x-lzx' => 'lzx',
            'text/x-m' => 'm',
            'audio/x-mpequrl' => 'm3u ',
            'application/x-troff-man' => 'man',
            'application/x-navimap' => 'map',
            'application/mbedlet' => 'mbd',
            'application/x-magic-cap-package-1.0' => 'mc$',
            'application/mcad' => 'mcd',
            'application/x-mathcad' => 'mcd',
            'image/vasa' => 'mcf',
            'text/mcf' => 'mcf',
            'application/netmc' => 'mcp',
            'application/x-troff-me' => 'me ',
            'application/x-frame' => 'mif',
            'application/x-mif' => 'mif',
            'www/mime' => 'mime',
            'audio/x-vnd.audioexplosion.mjuicemediafile' => 'mjf',
            'video/x-motion-jpeg' => 'mjpg',
            'application/x-meme' => 'mm',
            'audio/mod' => 'mod',
            'audio/x-mod' => 'mod',
            'audio/x-mpeg' => 'mp2',
            'video/x-mpeq2a' => 'mp2',
            'audio/mpeg3' => 'mp3',
            'audio/x-mpeg-3' => 'mp3',
            'application/vnd.ms-project' => 'mpp',
            'application/marc' => 'mrc',
            'application/x-troff-ms' => 'ms',
            'application/x-vnd.audioexplosion.mzz' => 'mzz',
            'application/vnd.nokia.configuration-message' => 'ncm',
            'application/x-mix-transfer' => 'nix',
            'application/x-conference' => 'nsc',
            'application/x-navidoc' => 'nvd',
            'application/oda' => 'oda',
            'application/x-omc' => 'omc',
            'application/x-omcdatamaker' => 'omcd',
            'application/x-omcregerator' => 'omcr',
            'text/x-pascal' => 'p',
            'application/pkcs10' => 'p10',
            'application/x-pkcs10' => 'p10',
            'application/pkcs-12' => 'p12',
            'application/x-pkcs12' => 'p12',
            'application/x-pkcs7-signature' => 'p7a',
            'application/x-pkcs7-certreqresp' => 'p7r',
            'application/pkcs7-signature' => 'p7s',
            'text/pascal' => 'pas',
            'image/x-portable-bitmap' => 'pbm',
            'application/vnd.hp-pcl' => 'pcl',
            'application/x-pcl' => 'pcl',
            'image/x-pict' => 'pct',
            'image/x-pcx' => 'pcx',
            'application/pdf' => 'pdf',
            'audio/make.my.funk' => 'pfunk',
            'image/x-portable-graymap' => 'pgm',
            'image/x-portable-greymap' => 'pgm',
            'application/x-newton-compatible-pkg' => 'pkg',
            'application/vnd.ms-pki.pko' => 'pko',
            'text/x-script.perl' => 'pl',
            'application/x-pixclscript' => 'plx',
            'text/x-script.perl-module' => 'pm',
            'application/x-portable-anymap' => 'pnm',
            'image/x-portable-anymap' => 'pnm',
            'model/x-pov' => 'pov',
            'image/x-portable-pixmap' => 'ppm',
            'application/powerpoint' => 'ppt',
            'application/x-mspowerpoint' => 'ppt',
            'application/x-freelance' => 'pre',
            'paleovu/x-pv' => 'pvu',
            'text/x-script.phyton' => 'py',
            'applicaiton/x-bytecode.python' => 'pyc',
            'audio/vnd.qcelp' => 'qcp',
            'video/x-qtc' => 'qtc',
            'audio/x-realaudio' => 'ra',
            'application/x-cmu-raster' => 'ras',
            'image/x-cmu-raster' => 'ras',
            'text/x-script.rexx' => 'rexx',
            'image/vnd.rn-realflash' => 'rf',
            'image/x-rgb' => 'rgb',
            'application/vnd.rn-realmedia' => 'rm',
            'audio/mid' => 'rmi',
            'application/ringing-tones' => 'rng',
            'application/vnd.nokia.ringing-tone' => 'rng',
            'application/vnd.rn-realplayer' => 'rnx',
            'image/vnd.rn-realpix' => 'rp',
            'text/vnd.rn-realtext' => 'rt',
            'application/x-rtf' => 'rtf',
            'video/vnd.rn-realvideo' => 'rv',
            'audio/s3m' => 's3m',
            'application/x-lotusscreencam' => 'scm',
            'text/x-script.guile' => 'scm',
            'text/x-script.scheme' => 'scm',
            'video/x-scm' => 'scm',
            'application/sdp' => 'sdp',
            'application/x-sdp' => 'sdp',
            'application/sounder' => 'sdr',
            'application/sea' => 'sea',
            'application/x-sea' => 'sea',
            'application/set' => 'set',
            'application/x-sh' => 'sh',
            'text/x-script.sh' => 'sh',
            'audio/x-psid' => 'sid',
            'application/x-sit' => 'sit',
            'application/x-stuffit' => 'sit',
            'application/x-seelogo' => 'sl',
            'audio/x-adpcm' => 'snd',
            'application/solids' => 'sol',
            'application/x-pkcs7-certificates' => 'spc',
            'application/futuresplash' => 'spl',
            'application/streamingmedia' => 'ssm',
            'application/vnd.ms-pki.certstore' => 'sst',
            'application/sla' => 'stl',
            'application/vnd.ms-pki.stl' => 'stl',
            'application/x-navistyle' => 'stl',
            'application/x-sv4cpio' => 'sv4cpio',
            'application/x-sv4crc' => 'sv4crc',
            'x-world/x-svr' => 'svr',
            'application/x-shockwave-flash' => 'swf',
            'application/x-tar' => 'tar',
            'application/toolbook' => 'tbk',
            'application/x-tcl' => 'tcl',
            'text/x-script.tcl' => 'tcl',
            'text/x-script.tcsh' => 'tcsh',
            'application/x-tex' => 'tex',
            'application/plain' => 'text',
            'application/gnutar' => 'tgz',
            'audio/tsp-audio' => 'tsi',
            'application/dsptype' => 'tsp',
            'audio/tsplayer' => 'tsp',
            'text/tab-separated-values' => 'tsv',
            'text/x-uil' => 'uil',
            'application/i-deas' => 'unv',
            'application/x-ustar' => 'ustar',
            'multipart/x-ustar' => 'ustar',
            'application/x-cdlink' => 'vcd',
            'text/x-vcalendar' => 'vcs',
            'application/vda' => 'vda',
            'video/vdo' => 'vdo',
            'application/groupwise' => 'vew',
            'application/vocaltec-media-desc' => 'vmd',
            'application/vocaltec-media-file' => 'vmf',
            'audio/voc' => 'voc',
            'audio/x-voc' => 'voc',
            'video/vosaic' => 'vos',
            'audio/voxware' => 'vox',
            'audio/x-twinvq' => 'vqf',
            'application/x-vrml' => 'vrml',
            'x-world/x-vrt' => 'vrt',
            'application/wordperfect6.1' => 'w61',
            'audio/wav' => 'wav',
            'audio/x-wav' => 'wav',
            'application/x-qpro' => 'wb1',
            'image/vnd.wap.wbmp' => 'wbmp',
            'application/vnd.xara' => 'web',
            'application/x-123' => 'wk1',
            'windows/metafile' => 'wmf',
            'text/vnd.wap.wml' => 'wml',
            'application/vnd.wap.wmlc' => 'wmlc',
            'text/vnd.wap.wmlscript' => 'wmls',
            'application/vnd.wap.wmlscriptc' => 'wmlsc',
            'application/x-wpwin' => 'wpd',
            'application/x-lotus' => 'wq1',
            'application/mswrite' => 'wri',
            'application/x-wri' => 'wri',
            'text/scriplet' => 'wsc',
            'application/x-wintalk' => 'wtk',
            'image/x-xbitmap' => 'xbm',
            'image/x-xbm' => 'xbm',
            'image/xbm' => 'xbm',
            'video/x-amt-demorun' => 'xdr',
            'xgl/drawing' => 'xgz',
            'image/vnd.xiff' => 'xif',
            'audio/xm' => 'xm',
            'application/xml' => 'xml',
            'text/xml' => 'xml',
            'xgl/movie' => 'xmz',
            'application/x-vnd.ls-xpix' => 'xpix',
            'image/xpm' => 'xpm',
            'video/x-amt-showrun' => 'xsr',
            'image/x-xwd' => 'xwd',
            'image/x-xwindowdump' => 'xwd',
            'application/x-compress' => 'z',
            'application/x-zip-compressed' => 'zip',
            'application/zip' => 'zip',
            'multipart/x-zip' => 'zip',
            'text/x-script.zsh' => 'zsh'
        ),

        'ambiguous' => array(
            'x-world/x-3dmf' => array('3dm', '3dmf', 'qd3', 'qd3d'),
            'application/octet-stream' => array(
                'a', 'arc', 'arj', 'bin', 'com', 'dump', 'exe', 'lha',
                'lhx', 'lzh', 'lzx', 'o', 'psd', 'saveme', 'uu', 'zoo'),
            'text/html' => array('html', 'acgi', 'htm', 'htmls', 'htx', 'shtml'),
            'application/postscript' => array('ps','ai', 'eps'),
            'audio/aiff' => array('aif', 'aifc', 'aiff'),
            'audio/x-aiff' => array('aiff', 'aifc', 'aif'),
            'video/x-ms-asf' => array('asf', 'asx'),
            'text/x-asm' => array('asm', 's'),
            'audio/basic' => array('au', 'snd'),
            'image/bmp' => array('bmp', 'bm'),
            'application/book' => array('boo', 'book'),
            'application/x-bzip2' => array('bz2','boz'),
            'application/x-bsh' => array('bsh','sh','shar'),
            'text/plain' => array('txt','c','c++','cc','conf','cxx','def','f','f90','for','g','h','hh','idc','jav','java','list','log','lst','m','mar','pl','sdml','text'),
            'text/x-c' => array('c','cc','cpp'),
            'application/x-netcdf' => array('cdf','nc'),
            'application/pkix-cert' => array('cer','crt'),
            'application/x-x509-ca-cert' => array('cer','crt','der'),
            'application/x-chat' => array('cha','chat'),
            'application/x-director' => array('dcr','dir','dxr'),
            'video/x-dv' => array('dif','dv'),
            'application/msword' => array('doc','dot','w6w','wiz','word'),
            'image/vnd.dwg' => array('dwg','dxf','svf'),
            'image/x-dwg' => array('dwg','dxf','svf'),
            'application/x-envoy' => array('env','evy'),
            'text/x-fortran' => array('f','f77','f90','for'),
            'image/florian' => array('flo','turbot'),
            'audio/make' => array('funk','my','pfunk'),
            'audio/x-gsm' => array('gsd','gsm'),
            'application/x-compressed' => array('gz','tgz','z','zip'),
            'application/x-gzip' => array('gz','gzip'),
            'text/x-h' => array('h','hh'),
            'application/x-helpfile' => array('help','hlp'),
            'application/vnd.hp-hpgl' => array('hgl','hpg','hpgl'),
            'image/ief' => array('ief','iefs'),
            'application/iges' => array('iges','igs'),
            'model/iges' => array('iges','igs'),
            'text/x-java-source' => array('java','jav'),
            'image/jpeg' => array('jpg','jfif','jfif-tbnl','jpe','jpeg'),
            'image/pjpeg' => array('jfif','jpe','jpeg','jpg'),
            'audio/midi' => array('kar','mid','midi'),
            'audio/nspaudio' => array('la','lma'),
            'audio/x-nspaudio' => array('la','lma'),
            'application/x-latex' => array('latex ','ltx'),
            'video/mpeg' => array('mpeg','m1v','m2v','mp2','mp3','mpa','mpe','mpg','mpeg4'),
            'audio/mpeg' => array('m2a','mp2','mpa','mpg','mpga'),
            'message/rfc822' => array('mime','mht','mhtml'),
            'application/x-midi' => array('mid','midi'),
            'audio/x-mid' => array('mid','midi'),
            'audio/x-midi' => array('mid','midi'),
            'music/crescendo' => array('mid','midi'),
            'x-music/x-midi' => array('mid','midi'),
            'application/base64' => array('mm','mme'),
            'video/quicktime' => array('mov','moov','qt'),
            'video/x-sgi-movie' => array('movie','mv'),
            'video/x-mpeg' => array('mp4', 'mp2', 'mp3'),
            'application/x-project' => array ('mpt','mpv','mpx'),
            'image/naplps' => array('nap','naplps'),
            'image/x-niff' => array ('niff'),
            'application/pkcs7-mime' => array('p7c'),
            'application/x-pkcs7-mime' => array('p7c','p7m'),
            'application/pro_eng' => array('part','prt'),
            'chemical/x-pdb' => array('pdb','xyz'),
            'image/pict' => array('pic','pict'),
            'image/x-xpixmap' => array('pm','xpm'),
            'application/x-pagemaker' => array('pm4','pm5'),
            'image/png' => array('png','x-png'),
            'application/mspowerpoint' => array('ppt','pot','pps','ppz'),
            'application/vnd.ms-powerpoint' => array('ppt','pot','ppa','pps','pwz'),
            'image/x-quicktime' => array('qtif'),
            'audio/x-pn-realaudio' => array('ra','ram','rm','rmm','rmp'),
            'audio/x-pn-realaudio-plugin' => array('ra','rmp','rpm'),
            'image/cmu-raster' => array('ras','rast'),
            'application/x-troff' => array ('t','tr'),
            'text/richtext' => array('rtf','rt','rtx'),
            'application/rtf' => array('rtf','rtx'),
            'application/x-tbook' => array('sbk ','tbk'),
            'text/sgml' => array('sgml'),
            'text/x-sgml' => array('sgm','sgml'),
            'application/x-shar' => array('sh','shar'),
            'text/x-server-parsed-html' => array('shtml','ssi'),
            'application/x-koan' => array('skd','skm','skt'),
            'application/smil' => array('smi','smil'),
            'text/x-speech' => array('spc','talk'),
            'application/x-sprite' => array('spr','sprite'),
            'application/x-wais-source' => array('src'),
            'application/step' => array('step','stp'),
            'application/x-world' => array('svr','wrl'),
            'application/x-texinfo' => array('texi','texinfo'),
            'image/tiff' => array('tif','tiff'),
            'image/x-tiff' => array('tif','tiff'),
            'text/uri-list' => array('uni','unis','uri','uris'),
            'text/x-uuencode' => array('uu','uue'),
            'video/vivo' => array('viv','vivo'),
            'video/vnd.vivo' => array('viv','vivo'),
            'audio/x-twinvq-plugin' => array('vqe','vql'),
            'model/vrml' => array('vrml','wrl','wrz'),
            'x-world/x-vrml' => array('vrml','wrl','wrz'),
            'application/x-visio' => array('vsd','vst','vsw'),
            'application/wordperfect6.0' => array('w60','wp5'),
            'application/wordperfect' => array('wp','wp5','wp6','wpd'),
            'application/excel' => array('xls','xl','xla','xlb','xlc','xld','xlk','xll','xlm','xlt','xlv','xlw'),
            'application/x-excel' => array('xls','xla','xlb','xlc','xld','xlk','xll','xlm','xlt','xlv','xlw'),
            'application/x-msexcel' => array('xls','xla','xlw'),
            'application/vnd.ms-excel' => array('xls','xlb','xlc','xll','xlm','xlw')
        )
    );


    /**
     * Array of HTML colors
     *
     * @var array
     */
    public static $colorNameA = array(
        'aliceblue'=>'F0F8FF',
        'antiquewhite'=>'FAEBD7',
        'aqua'=>'00FFFF',
        'aquamarine'=>'7FFFD4',
        'azure'=>'F0FFFF',
        'beige'=>'F5F5DC',
        'bisque'=>'FFE4C4',
        'black'=>'000000',
        'blanchedalmond '=>'FFEBCD',
        'blue'=>'0000FF',
        'blueviolet'=>'8A2BE2',
        'brown'=>'A52A2A',
        'burlywood'=>'DEB887',
        'cadetblue'=>'5F9EA0',
        'chartreuse'=>'7FFF00',
        'chocolate'=>'D2691E',
        'coral'=>'FF7F50',
        'cornflowerblue'=>'6495ED',
        'cornsilk'=>'FFF8DC',
        'crimson'=>'DC143C',
        'cyan'=>'00FFFF',
        'darkblue'=>'00008B',
        'darkcyan'=>'008B8B',
        'darkgoldenrod'=>'B8860B',
        'darkgray'=>'A9A9A9',
        'darkgreen'=>'006400',
        'darkgrey'=>'A9A9A9',
        'darkkhaki'=>'BDB76B',
        'darkmagenta'=>'8B008B',
        'darkolivegreen'=>'556B2F',
        'darkorange'=>'FF8C00',
        'darkorchid'=>'9932CC',
        'darkred'=>'8B0000',
        'darksalmon'=>'E9967A',
        'darkseagreen'=>'8FBC8F',
        'darkslateblue'=>'483D8B',
        'darkslategray'=>'2F4F4F',
        'darkslategrey'=>'2F4F4F',
        'darkturquoise'=>'00CED1',
        'darkviolet'=>'9400D3',
        'deeppink'=>'FF1493',
        'deepskyblue'=>'00BFFF',
        'dimgray'=>'696969',
        'dimgrey'=>'696969',
        'dodgerblue'=>'1E90FF',
        'firebrick'=>'B22222',
        'floralwhite'=>'FFFAF0',
        'forestgreen'=>'228B22',
        'fuchsia'=>'FF00FF',
        'gainsboro'=>'DCDCDC',
        'ghostwhite'=>'F8F8FF',
        'gold'=>'FFD700',
        'goldenrod'=>'DAA520',
        'gray'=>'808080',
        'green'=>'008000',
        'greenyellow'=>'ADFF2F',
        'grey'=>'808080',
        'honeydew'=>'F0FFF0',
        'hotpink'=>'FF69B4',
        'indianred'=>'CD5C5C',
        'indigo'=>'4B0082',
        'ivory'=>'FFFFF0',
        'khaki'=>'F0E68C',
        'lavender'=>'E6E6FA',
        'lavenderblush'=>'FFF0F5',
        'lawngreen'=>'7CFC00',
        'lemonchiffon'=>'FFFACD',
        'lightblue'=>'ADD8E6',
        'lightcoral'=>'F08080',
        'lightcyan'=>'E0FFFF',
        'lightgoldenrodyellow'=>'FAFAD2',
        'lightgray'=>'D3D3D3',
        'lightgreen'=>'90EE90',
        'lightgrey'=>'D3D3D3',
        'lightpink'=>'FFB6C1',
        'lightsalmon'=>'FFA07A',
        'lightseagreen'=>'20B2AA',
        'lightskyblue'=>'87CEFA',
        'lightslategray'=>'778899',
        'lightslategrey'=>'778899',
        'lightsteelblue'=>'B0C4DE',
        'lightyellow'=>'FFFFE0',
        'lime'=>'00FF00',
        'limegreen'=>'32CD32',
        'linen'=>'FAF0E6',
        'magenta'=>'FF00FF',
        'maroon'=>'800000',
        'mediumaquamarine'=>'66CDAA',
        'mediumblue'=>'0000CD',
        'mediumorchid'=>'BA55D3',
        'mediumpurple'=>'9370D0',
        'mediumseagreen'=>'3CB371',
        'mediumslateblue'=>'7B68EE',
        'mediumspringgreen'=>'00FA9A',
        'mediumturquoise'=>'48D1CC',
        'mediumvioletred'=>'C71585',
        'midnightblue'=>'191970',
        'mintcream'=>'F5FFFA',
        'mistyrose'=>'FFE4E1',
        'moccasin'=>'FFE4B5',
        'navajowhite'=>'FFDEAD',
        'navy'=>'000080',
        'oldlace'=>'FDF5E6',
        'olive'=>'808000',
        'olivedrab'=>'6B8E23',
        'orange'=>'FFA500',
        'orangered'=>'FF4500',
        'orchid'=>'DA70D6',
        'palegoldenrod'=>'EEE8AA',
        'palegreen'=>'98FB98',
        'paleturquoise'=>'AFEEEE',
        'palevioletred'=>'DB7093',
        'papayawhip'=>'FFEFD5',
        'peachpuff'=>'FFDAB9',
        'peru'=>'CD853F',
        'pink'=>'FFC0CB',
        'plum'=>'DDA0DD',
        'powderblue'=>'B0E0E6',
        'purple'=>'800080',
        'red'=>'FF0000',
        'rosybrown'=>'BC8F8F',
        'royalblue'=>'4169E1',
        'saddlebrown'=>'8B4513',
        'salmon'=>'FA8072',
        'sandybrown'=>'F4A460',
        'seagreen'=>'2E8B57',
        'seashell'=>'FFF5EE',
        'sienna'=>'A0522D',
        'silver'=>'C0C0C0',
        'skyblue'=>'87CEEB',
        'slateblue'=>'6A5ACD',
        'slategray'=>'708090',
        'slategrey'=>'708090',
        'snow'=>'FFFAFA',
        'springgreen'=>'00FF7F',
        'steelblue'=>'4682B4',
        'tan'=>'D2B48C',
        'teal'=>'008080',
        'thistle'=>'D8BFD8',
        'tomato'=>'FF6347',
        'turquoise'=>'40E0D0',
        'violet'=>'EE82EE',
        'wheat'=>'F5DEB3',
        'white'=>'FFFFFF',
        'whitesmoke'=>'F5F5F5',
        'yellow'=>'FFFF00',
        'yellowgreen'=>'9ACD32'
    );

    /**
     * Matches between recognized characters by date() and strftime() functions
     *
     * @var array
     */
    public static $date2strftime = array(
        'd' => '%d',
        'D' => '%a',
        'j' => '%e',
        'l' => '%A',
        'N' => '%u',
        'S' => '', // 'S' - is the 'st/nd/rd/th' day-suffix. There is no corresponding strftime-compatible character
        'w' => '%w',
        'z' => '%j', // (!) Note: 'z' - is from '0' to '365', in opposite to '%j' - from '001' to '366'
        'W' => '%W',
        'F' => '%B',
        'm' => '%m',
        'M' => '%b',
        'n' => '%m', // (!) Note that 'n' - is non-zero-based, in oppisite to '%m' - is zero-based,
        't' => '', // 't' - is the number of days on the given month. There is no corresponding strftime-compatible character
        'L' => '', // 'L' - is the leap year indicator. There is no corresponding strftime-compatible character
        'o' => '%g',
        'Y' => '%Y',
        'y' => '%y',
        'a' => '%p',
        'A' => '%P',
        'B' => '', // 'B' - Swatch Internet time. There is no corresponding strftime-compatible character
        'g' => '%l',
        'G' => '%k',
        'h' => '%I',
        'H' => '%H',
        'i' => '%M',
        's' => '%S',
        'u' => '', // 'u' - Microseconds. There is no corresponding strftime-compatible character
        'e' => '', // 'e' - Timezone identifier. There is no corresponding strftime-compatible character
        'I' => '', // 'I' - Daylight saving time flag. There is no corresponding strftime-compatible character
        'O' => '%z',
        'P' => '', // 'P' - Difference to Greenwich time (GMT) with colon between hours and minutes. There is no corresponding strftime-compatible character
        'T' => '%Z',
        'Z' => '', // 'Z' - Timezone offset in seconds. There is no corresponding strftime-compatible character
        'c' => '', // 'c' - ISO 8601 date. There is no corresponding strftime-compatible character
        'r' => '', // 'r' - RFC 2822 formatted date. There is no corresponding strftime-compatible character
        'U' => ''// 'U' - Seconds since the Unix Epoch (January 1 1970 00:00:00 GMT). There is no corresponding strftime-compatible character
    );

    /**
     * Compilation function source code, that will be passed to eval() function. Usage:
     * // 1. Setup a template for compiling
     * Indi::$cmpTpl = 'Hello <?=$user->firstName?>';
     * // 2. Call eval() within a scope, where $user object was defined. After eval() is finished, Indi::$cmpTpl is set to ''
     * eval(Indi::$cmpRun);
     * // 3. Get a compilation result
     * $compilationResult = Indi::cmpOut();
     *
     * @var string
     */
    public static $cmpRun = '
        $iterator = \'i\' . md5(microtime() . rand(0, 100000000));
        if (preg_match(\'/<\?|\?>/\', Indi::$cmpTpl)) {
            $php = preg_split(\'/(<\?|\?>)/\', Indi::$cmpTpl, -1, PREG_SPLIT_DELIM_CAPTURE);
            Indi::$cmpOut[$iterator] = \'\';
            for ($$iterator = 0; $$iterator < count($php); $$iterator++) {
                if ($php[$$iterator] == \'<?\') {
                    $php[$$iterator+1] = preg_replace(\'/^=/\', \' echo \', $php[$$iterator+1]) . \';\';
                    ob_start(); eval($php[$$iterator+1]); Indi::$cmpOut[$iterator] .= ob_get_clean();
                    $$iterator += 2;
                } else {
                    Indi::$cmpOut[$iterator] .= $php[$$iterator];
                }
            }
        } else if (preg_match(\'/(\$|::)/\', Indi::$cmpTpl)) {
            if (preg_match(\'/^\\\'/\', trim(Indi::$cmpTpl))) {
                Indi::$cmpTpl = ltrim(Indi::$cmpTpl, "\' ");
                if (preg_match(\'/\\\'$/\', trim(Indi::$cmpTpl)))
                    Indi::$cmpTpl = rtrim(Indi::$cmpTpl, "\' ");
                eval(\'Indi::$cmpOut[$iterator] = \\\'\' . Indi::$cmpTpl . \'\\\';\');
            } else {
                eval(\'Indi::$cmpOut[$iterator] = \\\'\' . Indi::$cmpTpl . \'\\\';\');
            }
        } else {
            Indi::$cmpOut[$iterator] = Indi::$cmpTpl;
        }
        Indi::$cmpTpl = \'\';
        ';

    /**
     * Pick the last item (containing last compiled value) from self::$cmpOut array, and reduce that array,
     * so it act like a stack
     *
     * @static
     * @return mixed
     */
    public static function cmpOut() {
        return array_pop(self::$cmpOut);
    }

    /**
     * Compiles a given template. This function should be called only in case if there is no context variables mentioned
     * in template, because otherwise there will be a fatal error with messages like 'Using $this when not in object
     * context' or 'Call to a member function somefunc() on a non-object'
     *
     * @static
     * @param $tpl
     * @return string
     */
    public static function cmp($tpl){
        $out = '';
        if (preg_match('/<\?|\?>/', $tpl)) {
            $php = preg_split('/(<\?|\?>)/', $tpl, -1, PREG_SPLIT_DELIM_CAPTURE);
            for ($i = 0; $i < count($php); $i++) {
                if ($php[$i] == '<?') {
                    $php[$i+1] = preg_replace('/^=/', ' echo ', $php[$i+1]) . ';';
                    ob_start(); eval($php[$i+1]); $out .= ob_get_clean();
                    $i += 2;
                } else {
                    $out .= $php[$i];
                }
            }
        } else if (preg_match('/(\$|::)/', $tpl)) {
            eval('$out = \'' . $tpl . '\';');
        } else {
            $out = $tpl;
        }

        return $out;
    }

    /**
     * Function is similar as jQuery .attr() function.
     * If only $key param is passed, the assigned value will be returned.
     * Otherwise, if $value param is also passed, this value will be placed in self::$_registry under $key key
     *
     * @static
     * @param $key
     * @param $value
     * @return mixed
     */
    public static function registry($key = null, $value = null) {

		// If only $key param passed, the assigned registry value will be returned
        if (func_num_args() == 1) return self::$_registry[$key];

        // Else if $value argument was given
        else if (func_num_args() == 2) {

			// If $value argument is null, unset the value from registry
			if ($value === null) unset(self::$_registry[$key]); 

			// Else placed it into registry under passed $key param.
			// If $value argument is an array, it will be converted to a new instance of ArrayObject class,
			// with setting ArrayObject::ARRAY_AS_PROPS flag for that newly created instance properties
			// to be also accessible as if they were an array elements
			else return self::$_registry[$key] = is_array($value)
				? new ArrayObject($value, ArrayObject::ARRAY_AS_PROPS)
				: $value;

        // Else if no arguments passed, return the whole registry
        } else if (func_num_args() == 0) return self::$_registry;
    }

    /**
     * Shortcut for Indi_Db::model() function
     * Loads the model by model's entity's id, or model class name
     *
     * @static
     * @param int|string $identifier
     * @param bool $check
     * @return Indi_Db_Table object
     */
    public static function model($identifier, $check = false) {

        // Call same method within Indi_Db object
        return Indi_Db::model($identifier, $check);
    }

    /**
     * Shortcut for Indi_Db::factory() function
     * Returns an singleton instance of Indi_Db
     * If an argument is presented, it will be passed to Indi_Db::factory() method, for, in it's turn,
     * usage as PDO connection properties
     *
     * @static
     * @return Indi_Db object
     */
    public static function db() {

        // Call 'factory' method of Indi_Db class, with first argument, if given. Otherwise just Indi_Db instance
        // will be returned, with no PDO configuration setup
        return Indi_Db::factory(func_num_args() ? func_get_arg(0) : null);
    }

    /**
     * Set or get values of all uri params or single param. If there is no value for 'uri' key in registry yet, setup it
     *
     * @static
     * @param null $key
     * @param null $value
     * @return mixed|null
     */
    public static function uri($key = null, $value = null){

        // If there is no value for 'uri' key in registry yet, we setup it
        if (is_null(Indi::store('uri'))) {

            // Create an *_Uri object
            $obj = class_exists('Project_Uri') ? new Project_Uri() : new Indi_Uri();

            // Push $obj object in registry under 'uri' key
            Indi::store('uri', $obj);
        }

        // If $key argument is null or not given, return value, stored under 'uri' key in registry
        if (is_null($key)) return Indi::store('uri');

        // Else if $key argument is not null and it is the single argument passed
        if (func_num_args() == 1)

            // If $key argument is an array or is an object - return value, stored under 'uri' key in registry,
            // Else we assume it is a property name within object, stored under 'uri' key in registry, so we
            // return value of $key key
            return is_array($key) || is_object($key) ? Indi::store('uri') : Indi::registry('uri')->$key;

        // Else if $value argument is given, we assign it to $key key within data, stored under 'uri' key in registry
        if (func_num_args() == 2) return Indi::registry('uri')->$key = $value;
    }

    /**
     * Short-hand access for current user object
     *
     * @static
     * @return mixed|null
     */
    public static function user(){

        // If there is no value for 'uri' key in registry yet, we setup it
        if (is_null(Indi::store('user'))) {

            // Get the current user row
            $userR = (int) $_SESSION['user']['id']
                ? Indi::model('User')->fetchRow('`id` = "' . (int) $_SESSION['user']['id'] . '"')
                : false;

            // Push $obj object in registry under 'uri' key
            Indi::store('user', $userR);
        }

        // Return current user object
        return Indi::store('user');
    }

    /**
     * Short-hand access for current cms user (admin) object
     *
     * @static
     * @param bool $refresh Mind whether $_SESSION['admin']['id'] still ok
     * @return mixed|null
     */
    public static function admin($refresh = false){

        // If there is no value for 'uri' key in registry yet, we setup it
        if (is_null(Indi::store('admin')) || $refresh) {

            // Get the database table name, where current cms user was found in
            $table = $_SESSION['admin']['alternate'] ?: 'admin';

            // Get the current user row
            $adminR = (int) $_SESSION['admin']['id']
                ? Indi::model($table)->fetchRow('`id` = "' . (int) $_SESSION['admin']['id'] . '"')
                : false;

            // If current visitor is not a cms/admin user - return
            if (!$adminR) return null;    
            
            // Setup 'alternate' property
            $adminR->alternate = $_SESSION['admin']['alternate'];

            // If current cms user was found not in 'admin' database table,  we explicilty setup foreign
            // data for 'profileId' foreign key, despite on in that other table may be not such a foreign key
            if ($table != 'admin') {
                $adminR->foreign('profileId', Indi::model('Profile')->fetchRow(
                    '`entityId` = "' . Indi::model($table)->id() . '"'
                ));
                $adminR->profileId = $adminR->foreign('profileId')->id;
            }

            // Push $obj object in registry under 'uri' key
            Indi::store('admin', $adminR);
        }

        // Return current user object
        return Indi::store('admin');
    }

    /**
     * Implode and compress files, mentioned in $files argument, under filename, constructed with usage of $alias
     * argument. This function is a part of performance improvement policy, which consists of:
     * 1. Imploding and gz-compressing all 'js' files in one, and all 'css' files in one, under filenames, ending with
     *    '.gz.css' and 'gz.css'
     * 2. Adding a .htaccess directive for additional header 'Content-Encoding: gzip' for such files
     *
     * In case if method is used for javascript files joining, and one or more of the files is actually not
     * javascript files, but php files - method assumes that such php files contain php constants definitions, such as
     *
     * <?php
     * define('CONST1_NAME', 'CONST1_VALUE');
     * define('CONST2_NAME', 'CONST2_VALUE');
     *
     * So, method will parse such php files and try to find name-value pairs for these constants. Then they will be
     * json-encoded, for being available as properties of special javascript object, accessible anywhere in the
     * javascript code. Here method additionally implements the namespaces concept, that allows to define the exact
     * place within the global javascript scope, where javascript object (containing json-encoded php constants) will
     * be accessible. To explicitly define that place, you should append the namespace definition after the php file
     * name, after colon - ':' - sign
     *
     * Example:
     *
     * Indi::implode(array(
     *   '/path/to/file1.js',
     *   '/path/to/file2.js',
     *   '/path/to/php-constants-file.php:mynamespace'
     * ))
     *
     * In this example, any php-constant will be accessible as simple as 'window.mynamespace.CONST1_NAME'
     *
     * If constants object should be placed within some other object, you can use ':some.other.object' as namespace
     * definition, so our example php constant will be accessible at 'window.some.other.object.CONST1_NAME'
     *
     * If that other object is not yet declared, it will be (recursively, if needed)
     *
     * If namespace definition is omitted, constant will be accessible within the global scope, e.g.
     * 'window.CONST1_NAME'
     *
     * This policy is a modified version of static content compression idea, and the difference is that 'gz.css' and
     * 'gz.js' files are ALREADY gzipped, so the server is not forced to compress them each time it receceives a request,
     * so it just flush it as is, but with special 'Content-Encoding' header, for them to be decompressed at client-side
     *
     * @param array $files
     * @param string $alias
     * @return int
     */
    public static function implode($files = array(), $alias = '') {

        // Get the type of files, here we assume that all files in $files argument have same type
        preg_match('/\.(css|js)$/', $files[0], $ext); $ext = $ext[1] ?: 'js';

        // Get the subdir name, relative to webroot
        $rel = '/' . $ext;

        // We set $refresh as false by default
        $refresh = false;

        // Get filename of file, containing modification times for all files that are compiled
        $mtime = DOC . STD . '/core' . (Indi::uri()->module == 'front' ? 'f' : '') . '/' . $rel . '/' . Indi::uri()->module . '/indi.all' . ($alias ? '.' . $alias : '') . '.mtime';

        // Append mirror files
        $mirrorA = array();
        for($i = 0; $i < count($files); $i++)
            foreach (ar('core,coref,www') as $place)
                if (is_file(DOC . STD . '/' . $place . preg_replace('/:[a-zA-Z\.$]+$/', '', $files[$i])))
                    $mirrorA[] = '/' . $place . $files[$i];

        // If this file does not exists, we set $refresh as true
        if (!file_exists($mtime)) {
            $refresh = true;

        // Else
        } else {

            // Get 'mtime' file contents and convert is to json, trimming namespaces
            $json = json_decode(file_get_contents($mtime), true);
            if (is_array($json)) foreach ($json as $file => $time) $noNsJson[preg_replace('/:[a-zA-Z\.$]+$/', '', $file)] = $time;

            // Prepare array containing file names with trimmed namespace definitions, for proper comparing
            for ($i = 0; $i < count($mirrorA); $i++) $noNsFiles[$i] = preg_replace('/:[a-zA-Z\.$]+$/', '', $mirrorA[$i]);

            // If $json is not an array, or is empty array, of files, mentioned in it do not match files in $files arg
            if (!is_array($json) || !count($json) || count(array_diff($noNsFiles, array_keys($noNsJson))) || count(array_diff(array_keys($noNsJson), $noNsFiles)))

                // We set $refresh as true
                $refresh = true;

            // Else we do a final check:
            else

                // If modification time  of at least one file in $files argument, is not equal to time,
                // stored in $json for that file, we set $refresh as true
                for ($i = 0; $i < count($noNsFiles); $i++)
                    if (filemtime(DOC . STD . $noNsFiles[$i]) != $noNsJson[$noNsFiles[$i]]) {
                        $refresh = true;
                        break;
                    }
        }

        // If after all these checks we discovered that compilation should be refreshed
        if ($refresh) {

            // Empty $json array
            $json = array();

            // Start output buffering
            ob_start();

            // Foreach file in $files argument
            for ($i = 0; $i < count($mirrorA); $i++) {

                // Get full file name
                $file = DOC . STD . preg_replace('/:[a-zA-Z\.$]+$/', '', $mirrorA[$i]);

                // Collect info about that file's modification time
                $json[$mirrorA[$i]] = filemtime($file);

                // If current file extension is 'php', we assume it's a file containing php constants definitions
                // We we need to parse such file contents and make it javascript-compatible by converting to JSON
                if (preg_match('/\.php(:[a-zA-Z\.$]+|)$/', $mirrorA[$i], $ns)) {

                    // Get the namespace. If no namespace defined - use 'window' by default
                    $ns = $ns[1] ? ltrim($ns[1], ':') : 'window';

                    // Get the php constants file contents
                    $php = file_get_contents($file);

                    // Pick constants names and values
                    preg_match_all('/define\(\'([A-Z0-9_]+)\', \'(.*)\'\);/', $php, $const);

                    // Define key-value-pairs $kvp array
                    $kvp = array();

                    // Fulfil $kvp array
                    for ($j = 0; $j < count($const[1]); $j++) $kvp[$const[1][$j]] = str_replace("\'", "'", $const[2][$j]);

                    // Provide namespace initialization
                    echo preg_match('/\./', $ns) ? "Ext.ns('$ns');" : ($ns != 'window' ? "var $ns = $ns || [];" : '');

                    // If namespace is given, and is 'Indi$lang'
                    if ($ns == 'Indi$lang') {
                        echo "$ns.push(" . json_encode($kvp) . ");";
                        echo "$ns.push({name: '" . Indi::ini('lang')->admin . "'});";
                    }

                // Echo that file contents
                } else if ($ext == 'css') {
                
                    // Get file contents
                    $txt = file_get_contents($file);
                    
                    // Get dir, relative to document root
                    $dir = pathinfo(preg_replace('~^/(www|coref|core)~', '', $mirrorA[$i]), PATHINFO_DIRNAME);
                    
                    // Convert relative paths, mentioned in css files to paths, relative to web root
                    $txt = preg_replace('!url\((\'|"|)/!', 'url($1' . STD . '/', $txt);
                    $txt = preg_replace('!url\((\'|"|)(\.\./)!', 'url($1' . STD . $dir .  '/$2', $txt);

                    // Remove comments from css
                    $txt = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $txt);

                    // Remove tabs, excessive spaces and newlines
                    $txt = str_replace(array("\r\n", "\r", "\n", "\t", '  ', '   '), '', $txt);
                    
                    // Flush
                    echo $txt;
                    
                // Flush file contents into output buffer
                } else readfile($file); 

                // Echo ';' if we deal with javascript files. Also flush double newline
                echo ($ext == 'js' ? ';' : '') . "\n\n";
            }

            // Refresh 'mtime' file for current compilation
            $fp = fopen($mtime, 'w'); fwrite($fp, json_encode($json)); fclose($fp);

            // Get output
            $txt = ob_get_clean();

            // If we currently deal with 'js' files
            if ($ext == 'js') {

                // Remove comments, return-carets, tabs and pseudo tabs (4 spaces) from js
                $txt = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $txt);
                $txt = preg_replace('!// .*!', '', $txt);
                $txt = str_replace(array("\r", '    ', "\t"), '', $txt);
            }

            // Compress compilation
            if ($alias != 'ie') $txt = gzencode($txt, 9);

            // Build the filename
            $gz = DOC . STD . '/core' . (Indi::uri()->module == 'front' ? 'f' : '')
                . $rel . '/' . Indi::uri()->module . '/indi.all' . ($alias ? '.' . $alias : '') . rif($alias != 'ie', '.gz') .'.' . $ext;

            // Refresh compilation file
            $fp = fopen($gz , 'w');
            fwrite($fp, $txt);
            fclose($fp);
        }

        // Return modification time for 'mtime' file
        return filemtime($mtime);
    }

    /**
     * Shortcut for easier access to an instance of Indi_View object, stored in registry
     *
     * @static
     * @return Indi_View|null
     */
    public static function view() {
        return Indi::store('view');
    }

    /**
     * This function does similar as Indi::registry() function, but is additionally able to set/get subkeys values for
     * data, stored in registry, if that data is of types 'array' or 'object'. Function was created to avoid almost
     * same coding for Indi::get(), Indi::post() and Indi::files() functions, so now these function use this function
     * instead of consisting of almost same code.
     *
     * @static
     * @param null $key
     * @param null $arg1
     * @param null $arg2
     * @return mixed|null
     */
    public static function store($key = null, $arg1 = null, $arg2 = null) {

        // If no $key argument was given - return whole registry
        if (is_null($key)) return Indi::registry();

        // If $key argument is not null and $arg1 argument is null - we get the value, stored in registry
        // under $key key, and return it
        if (is_null($arg1)) return Indi::registry($key);

        // Else if only $key and $arg1 arguments is passed, and they both are not null
        if (func_num_args() == 2)

            // We check is $arg1 an array or an object, and if so
            if (is_array($arg1) || is_object($arg1)) {

                // Set a value ($arg1) for $key key in registry, because the fact that $arg1 is array/object mean that
                // it is not a key, as arrays and objects are not allowed to be used as array keys or object properties
                return Indi::registry($key, $arg1);

            // Else if $arg1 argument is not an array or object, we assume that it is a subkey, so we return it's value
            } else return Indi::store($key)->$arg1;

        // Else if three arguments passed, we assume that they are key, subkey and value, so we set a value, got from
        // third argument under a subkey (second argument), under a $key key in registry and after that return that value
        else if (func_num_args() == 3) return Indi::store($key)->$arg1 = $arg2;
    }

    /**
     * Set or gets $_GET params as single param or as whole array, converted to instance of ArrayObject class.
     * Usage:
     * 1.Indi::get();               //   ArrayObject (
     *                              //       [param1] => value1
     *                              //       [param2] => value2
     *                              //   )
     * 2.Indi::get()->param1        //   value1
     * 3.Indi::get()->param1 = 1234 //   1234
     * 4.Indi::get()->param1        //   1234
     * 5.Indi::get('param1')        //   1234
     * 6.Indi::get('param1', 12345) //   12345
     * 7.Indi::get('param1')        //   12345
     * 8.$myGetCopy = Indi::get();  //   ArrayObject (
     *                              //       [param1] => 12345
     *                              //       [param2] => value2
     *                              //   )
     * 9.$myGetCopy['param1']       //   12345
     * 10. $myGetCopy->param1       //   12345
     *
     * For initial (and further, if need) setting, use Indi::get($_GET)
     *
     * @static
     * @param null $arg1
     * @param null $arg2
     * @return mixed
     */
    public static function get($arg1 = null, $arg2 = null) {
        return func_num_args() == 1 ? Indi::store('get', $arg1) : Indi::store('get', $arg1, $arg2);
    }

    /**
     * Set or gets $_POST params as single param or as whole array, converted to instance of ArrayObject class.
     * Usage - same as for Indi::get() function
     *
     * @static
     * @param null $arg1
     * @param null $arg2
     * @return mixed
     */
    public static function post($arg1 = null, $arg2 = null) {
        return func_num_args() == 1 ? Indi::store('post', $arg1) : Indi::store('post', $arg1, $arg2);
    }

    /**
     * Set or gets $_FILES params as single param or as whole array, converted to instance of ArrayObject class.
     * Usage - same as for Indi::get() function
     *
     * @static
     * @param null $arg1
     * @param null $arg2
     * @return mixed
     */
    public static function files($arg1 = null, $arg2 = null) {
        return func_num_args() == 1 ? Indi::store('files', $arg1) : Indi::store('files', $arg1, $arg2);
    }

    /**
     * Setup a proper order of elements in $setA array, depending on their titles
     *
     * @static
     * @param $entityId
     * @param $idA
     * @param string $dir
     * @return array
     */
    public static function order($entityId, $idA, $dir = 'ASC'){

        // Load the model
        $model = Indi::model($entityId);

        // Get the columns list
        $columnA = $model->fields(null, 'aliases');

        // Determine title column name
        if ($titleColumn = $model->comboDataOrder ?: current(array_intersect($columnA, array('title', '_title')))) {

            // Check whether $titleColumn contains some expression rather than just some column name,
            // and if so - use it as is but strip '$dir' from it or replace with actual direction ($dir)
            // else wrap $titleColumn with '`' and append $dir
            $expr = preg_match('~^[a-zA-Z0-9]+$~', $titleColumn)
                ? '`' . $titleColumn . '` ' . $dir
                : str_replace('$dir', $dir, $titleColumn);

            // Setup a new order for $idA
            $idA = Indi::db()->query('
                SELECT `id`
                FROM `' . $model->table() . '`
                WHERE `id` IN (' . implode(',', $idA) . ')
                ORDER BY ' . $expr . '
            ')->fetchAll(PDO::FETCH_COLUMN);
        }

        // Return reordered ids
        return $idA;
    }
	
    /**
     * Return an array containing defined constants, which are lang-constants at most
     *
     * @static
     * @param boolean $json
     * @return array|json
     */
	public static function lang($json = false) {

        // Define $langA array
        $langA = array();

        // Foreach defined constants check if constant name starts with 'I_', and if so - append it to $langA array
		foreach (get_defined_constants() as $name => $value)
            if (preg_match('/^I_/', $name))
                $langA[$name] = $value;

        // Return lang constants as an array, optionally encoded to json, depending on $json argument is boolean true
		return $json ? json_encode($langA) : $langA;
	}

    /**
     * Try to localize using transliteration
     *
     * @static
     * @param $text
     * @param $to
     * @param null $from
     * @return mixed
     */
    public static function l10n($text, $to, $from = null) {

        // If $from arg is not given - set it to be same as Indi::ini('lang')->admin
        if (!$from) $from = Indi::ini('lang')->admin;

        // If given lang is same as current lang return $text as is
        if ($to == $from) return $text;

        // If we need to convert from Russian to English
        if ($from == 'ru' && $to == 'en') {

            // Symbols
            $ru = array(
                'а','б','в','г','д','е','ё','ж','з','и','й','к','л','м','н','о','п',
                'р','с','т','у','ф','х','ц','ч','ш','щ','ъ','ы','ь','э','ю','я',
                'А','Б','В','Г','Д','Е','Ё','Ж','З','И','Й','К','Л','М','Н','О','П',
                'Р','С','Т','У','Ф','Х','Ц','Ч','Ш','Щ','Ъ','Ы','Ь','Э','Ю','Я'
            );

            // Replacements
            $en = array(
                'a','b','v','g','d','e','yo','zh','z','i','i','k','l','m','n','o','p',
                'r','s','t','u','f','h','c','ch','sh','shh','','y','','e','yu','ya',
                'A','B','V','G','D','E','Yo','Zh','Z','I','I','K','L','M','N','O','P',
                'R','S','T','U','F','H','C','Ch','Sh','Shh','','Y','','E','Yu','Ya',
            );

            // Combine
            $ex = array_combine($ru, $en);

            // Replace
            return preg_replace_callback('/('. im($ru, '|') .')/', function($m) use ($ex) {
                return $ex[$m[1]];
            }, $text);

        // If we need to convert from English to Russian
        } else if ($from == 'en' && $to == 'ru') {

            // Replacements
            $en = array(
                'sh','shh',/*'',*/'uy',/*'',*/'ye','yu','ya','j','sch','sck','ch','hn','th',
                'a','b','v','g','d','e','yo','zh','z','i','y','k','l','m','n','o','p',
                'r','s','t','u','f','h','c',
                'Sh','Shh',/*'',*/'Uy',/*'',*/'Ye','Yu','Ya','J','Sch','Sck','Ch','Hn','Th',
                'A','B','V','G','D','E','Yo','Zh','Z','I','Y','K','L','M','N','O','P',
                'R','S','T','U','F','H','C',
            );

            // Symbols
            $ru = array(
                'ш','щ',/*'ъ',*/'ы',/*'ь',*/'э','ю','я','дж','шк','шк','ч','н','т',
                'а','б','в','г','д','е','ё','ж','з','и','й','к','л','м','н','о','п',
                'р','с','т','у','ф','х','ц',

                'Ш','Щ',/*'Ъ',*/'Ы',/*'Ь',*/'Э','Ю','Я','Дж','Шк','Шк','Ч','Н','Т',
                'А','Б','В','Г','Д','Е','Ё','Ж','З','И','Й','К','Л','М','Н','О','П',
                'Р','С','Т','У','Ф','Х','Ц',
            );

            // Combine
            $ex = array_combine($en, $ru);

            // Replace
            return preg_replace_callback('/('. im($en, '|') .')/', function($m) use ($ex) {
                return $ex[$m[1]];
            }, $text);

        // Else return as is
        } else return $text;
    }

    /**
     * Converts an html color name to a hex color value
     *
     * @static
     * @param $color
     * @return string
     */
    public static function hexColor($color) {

        // Remove the spaces, and leading '#', if presented
        $color = ltrim(trim($color), '#');

        // If $color is a hex color in format 'rrggbb', we return it as is
        if (preg_match('/^([a-fA-F0-9]{6})$/', $color, $match)) {
            return $match[1];

        // Else if $color is a hex color, but in format 'rgb' we convert it to 'rrggbb' format
        } else if (preg_match('/^([a-fA-F0-9])([a-fA-F0-9])([a-fA-F0-9])$/', $color, $match)) {
            $hex = ''; for ($i = 1; $i < 4; $i++) $hex .= $match[$i] . $match[$i]; return $hex;

        // Else we'll try to find a match within self::$colorNameA array, containing 147 standard HTML color names
        } else {

            // Convert color name to lowercase
            $color = strtolower($color);

            // If found, return it, with '#' prefix, else return empty string
            return ($hex = self::$colorNameA[$color]) ? '#' . $hex : '';
        }
    }

	/**
	 * Fetch rowset from `staticblock` table and return it as an assotiative array with aliases as keys.
	 * Rows in `staticblock` table store some text phrases and settings, so function provide and ability to
	 * access it from anywhere. Rowset fetch will be only done at first function call.
	 *
     * @param string $key
     * @param string $default A value, that will be returned if $key will not be found in self::$_blockA array
	 * @return array
	 */
	public static function blocks($key = null, $default = null){

		// If self::$_blockA is null at the moment, we fetch it from `staticblock` table
		if (self::$_blockA === null) {

			// Setup self::$_blockA as an empty array at first
			self::$_blockA = array();

			// Fetch rowset
            $w = Indi::uri()->staticpageAdditionalWHERE; $w[] = '`toggle` = "y"';
            $staticBlockRs = Indi::model('Staticblock')->fetchAll($w);
			
			// Setup values in self::$_blockA array under certain keys
            foreach ($staticBlockRs as $staticBlockR) {
                self::$_blockA[$staticBlockR->alias] = $staticBlockR->{'details' . ucfirst($staticBlockR->type)};
                if ($staticBlockR->type == 'textarea') self::$_blockA[$staticBlockR->alias] = nl2br(self::$_blockA[$staticBlockR->alias]);
            }
		}

        // Check if $key is a regexp, and if yes
        if (is_string($key) && Indi::rexm('/^(\/|#|\+|%|~|!)[^\1]*\1[imsxeu]*$/', $rex = $key)) {

            // Collect values under keys that match a regular expression
            foreach (self::$_blockA as $alias => $value)
                if (preg_match($rex, $alias))
                    $blockA[$alias] = Indi::blocks($alias);

            // Return array of values
            return $blockA ?: array();
        }

        // Check whether current block's content contains other blocks placeholders, and if found
        if (preg_match_all('/{[a-zA-Z0-9\-]+}/', self::$_blockA[$key], $m))

            // Foreach found placeholder
            foreach ($m[0] as $placeholder)

                // Trim '{}' chars from placeholder, for usage as an other block's key, and prevent recursion
                if (($bkey = trim($placeholder, '{}')) != $key)

                    // Do replacement
                    self::$_blockA[$key] = str_replace($placeholder, Indi::blocks($bkey) ?: '', self::$_blockA[$key]);

		// If $key argument was specified, we return a certain value, or all array otherwise
		return $key == null ? self::$_blockA : (array_key_exists($key, self::$_blockA) ? self::$_blockA[$key] : $default);
	}

    /**
     * Parses ini file given by $arg argument, convert it from array to ArrayObject and save into the registry
     * If $arg agrument does not end with '.ini', it will be interpreted as a key, so it's value will be returned
     * If $arg argument is not given or null, the whole ini ArrayObject object, that represents ini file contents
     * will be returned
     *
     * @static
     * @param null $arg
     * @return mixed|null
     */
    public static function ini($arg = null) {

        // If $arg argument is a path end with '.ini', and file with that path exists
        if (preg_match('/\.ini$/', $arg) && is_file($arg)) {

            // Parse ini file
            $parsed = parse_ini_file($arg, true);

            // Create empty instance of stdClass
            $ini = new stdClass();

            // Foreach section
            foreach ($parsed as $section => $params) {

                // Setup section as new instance of stdClass
                $ini->$section = new stdClass;

                // Foreach section's param
                foreach ($params as $key => $value) {

                    // Get the copy of current section
                    $c = $ini->$section;

                    // Foreach dot-separated sub-key name within $key
                    foreach (explode('.', $key) as $key) {

                        // If $c->$key is not yet set - setup it as new instance of stdClass
                        if (!isset($c->$key)) $c->$key = new stdClass();

                        // Setup previous param
                        $prev = $c;

                        // Shift nesting
                        $c = $c->$key;
                    }

                    // Setup value
                    $prev->$key = $value;
                }
            }

            // Save into the registry
            return Indi::registry('ini', $ini);
        }

        // Else if $arg argument is a string, we assume that it is a key, so we return it's value
        else if (is_string($arg)) return Indi::store('ini')->$arg;

        // Else we return the whole ini object
        else if (!$arg) return Indi::store('ini');
    }

    /**
     * Return regular expressions pattern, stored within $this->_rex property under $alias key
     *
     * @param $alias
     * @return null
     */
    public static function rex($alias){
        return $alias ? self::$_rex[$alias] : null;
    }


    /**
     * Call preg_match() using pattern, stored within Indi::$_rex array under $rex key and using given $subject.
     * If no pre-defined pattern found in Indi::$_rex under $rex key, function will assume that $rex is a regular
     * expression.
     *
     * @static
     * @param $rex
     * @param $subject
     * @param null $sub If regular expression contains submask(s), $sub arg can be used as
     *                  a way to specify a submask index, that you need to pick the value at
     * @return array|null|string
     */
    public static function rexm($rex, $subject, $sub = null){

        // Check that self::$_rex array has a value under $alias key
        if ($_ = Indi::rex($rex)) $rex = $_;

        // Match
        preg_match($rex, $subject, $found);

        // Return
        return $found ? (func_num_args() == 3 ? $found[$sub] : $found) : ($found ?: '');
    }

    /**
     * Call preg_match_all() using pattern, stored within Indi::$_rex array under $rex key and using given $subject
     *
     * @static
     * @param $rex
     * @param $subject
     * @return array|int
     */
    public static function rexma($rex, $subject) {

        // Check that self::$_rex array has a value under $alias key
        if ($_ = Indi::rex($rex)) $rex = $_;

        // Match
        $success = preg_match_all($rex, $subject, $found);

        // Return
        return $success ? $found : $success;
    }

    /**
     * Shortcut for Indi_Trail_Admin. Usage:
     *
     * Indi::trail(true) - whole Indi_Trail_Admin object
     * Indi::trail()->row/section/sections/filters/grid/etc.
     * Indi::trail(1)->row - goes to parent trail item
     *
     * @static
     * @param null $arg
     * @param Indi_Controller $arg2
     * @return Indi_Trail_Admin|Indi_Trail_Admin_Item
     */
    public static function trail($arg = null, Indi_Controller $arg2 = null) {

        // If $arg argument is an array, we assume that it's a route stack, so we create a new trail object and store
        // it into the registry
        if (is_array($arg)) {
            $class = 'Indi_Trail_' . ucfirst(Indi::uri()->module);
            return Indi::registry('trail', new $class($arg, $arg2));
        }

        // Else if $arg argument is boolean 'true', we return the whole trail object
        else if ($arg === true) return Indi::registry('trail');

        // Else if registry contains valid trail object
        else if (is_object(Indi::registry('trail')))

            // If $arg argument is not set, we return current trail item object
            // Else we return item, that is at index, shifted from the last index by $arg number. The $arg argument will
            // be casted as integer by '(int)' expression in 'item()' method call
            return $arg == null ? Indi::registry('trail')->item() : Indi::registry('trail')->item($arg);

        // Else print backtrace, as the fact that we are here mean that we faced an attempt to call method item()
        // on a non-object, and standard error message is not usefult here, as doesn't give any backtrace
        else {

            // Print backtrace
            debug_print_backtrace();

            // Die
            iexit();
        }
    }

    /**
     * Load cache files if need
     *
     * @static
     */
    public static function cache(){
        Indi_Cache::load();
    }

    /**
     * Build and return an image (represented by 'img' tag), related to certain row of certain entity,
     * or the certain copy of that image, if $copy argument is given.
     *
     * @static
     * @param string $entity
     * @param int $id
     * @param string $field
     * @param string $copy
     * @param array $attr
     * @return string
     */
    public static function img($entity, $id, $field, $copy = '', $attr = array()) {

        // If $copy argument is an array, we assume that it is used as $attr argument.
        // Such implementation is bit more short-handy, because expression
        // Indi::img('myentity', 123, 'imagefield', array('height' => 200)) is more friendly than
        // Indi::img('myentity', 123, 'imagefield', null, array('height' => 200))
        if (is_array($copy)) {
            $attr = $copy;
            $copy = '';
        }

        // Get the directory name
        $dir = DOC . STD . '/' . Indi::ini()->upload->path . '/' . $entity . '/';

        // If directory does not exist - return
        if (!is_dir($dir)) return;

        // Get the image full filename
        list($abs) = glob($dir . $id . '_' . $field . ($copy ? ',' . $copy : '') . '.{gif,jpeg,jpg,png}', GLOB_BRACE);

        // If no image found - return
        if (!$abs) return;

        // Setup 'src' attribute
        $attr['src'] = substr($abs, strlen(DOC)) . '?' . substr(filemtime($abs), -3);

        // Setup empty alt attribute
        if (!isset($attr['alt'])) $attr['alt'] = '';

        // Build attributes string
        $attrA = array(); foreach ($attr as $a => $v) $attrA[] = $a . '="' . str_replace('"', '\"', $v) . '"';

        // Build and return img tag
        return '<img ' . implode(' ', $attrA) . '/>';
    }

    /**
     * Build and return a shockwave flash object (represented by 'embed' tag), related to certain row of certain entity
     *
     * @static
     * @param string $entity
     * @param int $id
     * @param string $field
     * @param array $attr
     * @return string
     */
    public static function swf($entity, $id, $field, $attr = array()) {

        // Get the directory name
        $dir = DOC . STD . '/' . Indi::ini()->upload->path . '/' . $entity . '/';

        // If directory does not exist - return
        if (!is_dir($dir)) return;

        // Get the image full filename
        list($abs) = glob($dir . $id . '_' . $field . '.swf');

        // If no image found - return
        if (!$abs) return;

        // Setup 'src' attribute
        $attr['src'] = substr($abs, strlen(DOC)) . '?' . substr(filemtime($abs), -3);

        // Setup specific attributes
        $attr['type'] = 'application/x-shockwave-flash';
        $attr['pluginspace'] = 'http://www.macromedia.com/go/getflashplayer';
        $attr['play'] = 'true';
        $attr['loop'] = 'true';
        $attr['menu'] = 'true';

        // If 'width' attribute is not set or 'height' attribute is not set
        if (!$attr['width'] || !$attr['height']) {

            // Get the real size of flash object
            list($real['width'], $real['height']) = getflashsize($abs);

            // If both 'width' and 'height' attributes are not set - set them same as real width and height
            if (!$attr['width'] && !$attr['height']) $attr = array_merge($attr, $real);

            // Else if 'width' attribute was set - calculate and setup 'height' attribute
            else if ($attr['width']) $attr['height'] = ceil($real['height']/$real['width']*$attr['width']);

            // Else if 'height' attribute was set - calculate and setup 'width' attribute
            else if ($attr['height']) $attr['width'] = ceil($real['width']/$real['height']*$attr['height']);
        }

        // Build attributes string
        $attrA = array(); foreach ($attr as $a => $v) $attrA[] = $a . '="' . str_replace('"', '\"', $v) . '"';

        // Build and return img tag
        return '<embed ' . implode(' ', $attrA) . '/>';
    }

    /**
     * Get file extension by mime-type
     *
     * @static
     * @param $mime
     * @return string
     */
    public static function ext($mime) {

        // If value of $mime argument was found as a key within self::$_mime['definitive'] array - return extension
        if (isset(self::$_mime['definitive'][$mime])) return self::$_mime['definitive'][$mime];

        // Else if value of $mime argument was found as a key within self::$_mime['ambiguous'] array - return first extension
        else if (isset(self::$_mime['ambiguous'][$mime])) return self::$_mime['ambiguous'][$mime][0];

        // Else if still no extension got - return 'unknown'
        else return 'unknown';
    }

    /**
     * Try to detect the mimetype according to extension, given in $ext argument.
     * $ext arguments can also be a filename, in that case function will preliminary doa try to
     * detect the mimetype using php's Fileinfo extension, and if that try fails - use usual
     * detection logic
     *
     * @param $ext
     * @return string
     */
    public static function mime($ext) {

        // If $ext argument seems to be a file name
        if (preg_match('/\./', $ext)) {

            // If that file exists, and php's Fileinfo extensions is enabled, and finfo resource was created
            if (is_file($ext) && function_exists('finfo_open') && $finfo = finfo_open(FILEINFO_MIME_TYPE)) {

                // Get the mimetype
                $mime = finfo_file($finfo, $ext);

                // Close finfo resource
                finfo_close($finfo);

                // Return mimetype
                return $mime;

            // Get the extension from filename
            } else $ext = array_pop(explode('.', $ext));
        }

        // If extension was found in definitive list of mimetype-extension pairs - return mimetype
        if ($mime = array_search($ext, self::$_mime['definitive'])) return $mime;

        // Then try to find mimetype in ambiguous list of mimetype-extension pairs
        foreach (self::$_mime['ambiguous'] as $mime => $extA)

            // Return mimetype if extension found
            if (in_array($ext, $extA)) return $mime;

        // Return 'unknown/unknown'
        return 'unknown/unknown';
    }

    /**
     * Get the info about contents, that remote host may response with in case of request - size in bytes,
     * mime-type and file extension. Actually, this function is used as preliminary check before *_Row->wget()
     * calls, e.g. it used to detect remote file size and type, as it is useful information for making a decision
     * on whether or not start downloading the actual contents of a remote file
     *
     * @static
     * @param $url
     * @return array
     */
    public static function probe($url) {

        // Check if $url's host name is same as $_SERVER['HTTP_HOST']
        $purl = parse_url($url); $isOwnUrl = $purl['host'] == $_SERVER['HTTP_HOST'] || !$purl['host'];

        // If hostname is not specified within $url, prepend $url with self hostname and PRE constant
        if (!$purl['host']) $url = 'http://' . $_SERVER['HTTP_HOST'] . STD . $url;

        // Create curl resource
        $ch = curl_init($url);

        // Setup options
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HEADER, TRUE);
        curl_setopt($ch, CURLOPT_NOBODY, TRUE);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);

        // If so
        if ($isOwnUrl) {

            // Setup cookie
            curl_setopt($ch, CURLOPT_COOKIE, 'PHPSESSID=' . session_id());

            // Write session data and end session, to prevent execution freeze
            session_write_close();
        }

        // Execute
        $response = curl_exec($ch);

        // Restart session
        if ($isOwnUrl) session_start();

        // Get size and mime-type
        $size = curl_getinfo($ch, CURLINFO_CONTENT_LENGTH_DOWNLOAD);
        $mime = array_shift(explode(';', curl_getinfo($ch, CURLINFO_CONTENT_TYPE)));

        // Close curl resource
        curl_close($ch);

        // Try to detect appropriate file extension, using Content-Disposition header, if exists in response
        $headers = http_parse_headers($response);
        foreach ($headers as $header => $value)
            if (preg_match('/Content-Disposition/', $header))
                $ext = array_pop(explode('.', trim(array_pop(explode('=',array_pop(explode(';', $value)))), '"\'')));

        // If no extension detected, try to detect it using mime-type
        if (!$ext) $ext = Indi::ext($mime);

        // Return info
        return array('size' => $size, 'mime' => $mime, 'ext' => $ext);
    }

    /**
     * This function is useful for short-hand access to values, passed within json_encoded value of
     * $_GET's 'search' param
     *
     * @static
     * @param null $arg1
     * @param null $arg2
     * @return array
     */
    public static function obar($arg1 = null, $arg2 = null) {

        // Define $obar array, that will contain key->value pairs for all involved filters
        $obar = array();

        // If there is no 'search' param within query string - set up it as json-encoded empty array
        if (!Indi::get('search')) Indi::get('search', json_encode($obar));

        // Json-decode $_GET's 'search' param
        $rawA = json_decode(Indi::get('search'), true);

        // If Json-encoded string was invalid - return empty array
        if (!is_array($rawA)) return array();

        // Build the $obar array
        foreach ($rawA as $rawI) $obar[key($rawI)] = current($rawI);

        // If no arguments given - return $_GET's search param as a usage-friendly array
        if (func_num_args() == 0) return $obar;

        // Else if single argument given - assume it's a key within $obar, and return it's value
        else if (func_num_args() == 1) return $obar[func_get_arg(0)];

        // Else if two arguments given - assume it's a key and a value, that should be assigned to a key
        else if (func_num_args() == 2) {

            // Assign the value
            $obar[$arg1] = $arg2;

            // Prepare a new array, that will be used a replacement for $_GET's 'search' param
            $rawA = array(); foreach ($obar as $k => $v) $rawA[] = array($k => $v);

            // Replace, so Indi::get()->search will reflect new value ($arg2 argument)
            // assignment for a given key ($arg1 argument)
            Indi::get()->search = json_encode($rawA);

            // Return assigned value
            return $obar[$arg1];
        }
    }

    /**
     * Converts a given string to version, representing this string as is it was typed
     * in a different keyboard layout. The 'kl' abbreviation mean 'keyboard layout'
     *
     * @static
     * @param string $string
     * @param null|string $l - layout to convert to, e.g. 'ru', 'en' or others
     * @return string
     */
    public static function kl($string, $l = null) {

        // Define object, containing characters that are located on the
        // same keyboard buttons, but within another keyboard layouts
        $kl = array(

            // Define an array for english alphabetic characters
            'en' => array('~','Q','W','E','R','T','Y','U','I','O','P','{','}',
                'A','S','D','F','G','H','J','K','L',':','"',
                'Z','X','C','V','B','N','M','<','>',

                '`','q','w','e','r','t','y','u','i','o','p','[',']',
                'a','s','d','f','g','h','j','k','l',';',"'",
                'z','x','c','v','b','n','m',',','.'),

            // Define an array for russian alphabetic characters
            'ru' =>  array('Ё','Й','Ц','У','К','Е','Н','Г','Ш','Щ','З','Х','Ъ',
                'Ф','Ы','В','А','П','Р','О','Л','Д','Ж','Э',
                'Я','Ч','С','М','И','Т','Ь','Б','Ю',

                'ё','й','ц','у','к','е','н','г','ш','щ','з','х','ъ',
                'ф','ы','в','а','п','р','о','л','д','ж','э',
                'я','ч','с','м','и','т','ь','б','ю')
        );

        // Define a variable for converted equivalent, and index variable
        $converted = ''; $names = array_keys($kl);

        // For each character within given string find its equivalent and append to 'converted' variable
        for ($i = 0; $i < mb_strlen($string, 'utf-8'); $i++) {

            // Get character
            $c = mb_substr($string, $i, 1, 'utf-8');

            // Define auxiliary variables
            $src = ''; $dst = ''; $at = null;

            // Define/reset and detect character source keyboard layout, and reset destination layout
            for ($k = 0; $k < count($names); $k++)
                if (($j = array_search($c, $kl[$names[$k]])) !== false) {
                    $src = $names[$k];
                    $at = $j;
                }

            // If no source was detected - try another next character
            if (!$src) $converted .= $c; else {

                // If $l argument is given - setup $dst variable with the value of $l argument
                if ($l) $dst = $l;

                // Else if source layout differs from current language
                // - setup current language as destination layout
                else if ($src != Indi::ini('lang')->admin) $dst = Indi::ini('lang')->admin;

                // Else if source layout is 'ru' - setup destination layout as 'en'
                else if ($src == 'ru') $dst = 'en';

                // Get converted character
                if ($dst) $converted .= $kl[$dst][$at];
            }
        }

        // Return converted string
        return $converted;
    }

    /**
     * Check if $dir directory exists and/or try to create it, if $mode argument is not 'exists'.
     * If directory creation attempt would fail - function will return an error message
     *
     * @static
     * @param $dir
     * @param string $mode
     * @return bool|string
     */
    public static function dir($dir, $mode = '') {

        // Check if target directory exists, and if no
        if (!is_dir($dir)) {

            // If $mode argument is 'exists', it mean that directory is not exist, so we return boolean false
            if ($mode == 'exists') return false;

            // Foreach directories tree branches from the desired and up to the project root
            do {

                // Get the upper directory
                $level = preg_replace(':[^/]+/$:', '', isset($level) ? $level : $dir);

                // If upper directory exists
                if (is_dir($level)) {

                    // If upper directory is writable
                    if (is_writable($level)){

                        // If for some reason attempt to recursively create target directory,
                        // starting from current level - was unsuccessful
                        if (!@mkdir($dir, 0777, true)) {

                            // Get the target directory part, that is relative to current level
                            $rel = str_replace($level, '', $dir);

                            // Return an error
                            return sprintf(I_ROWFILE_ERROR_MKDIR, $rel, $level);
                        }

                    // Else if upper directory is not writable
                    } else {

                        // Get the target directory part, that is relative to current level
                        $rel = str_replace($level, '', $dir);

                        // Return an error
                        return sprintf(I_ROWFILE_ERROR_UPPER_DIR_NOT_WRITABLE, $rel, $level);
                    }

                    // Break the loop
                    break;
                }
            } while ($level != DOC . STD . '/');

        // Else if target directory exists, but is not writable - return an error
        } else if (!is_writable($dir)) return sprintf(I_ROWFILE_ERROR_TARGET_DIR_NOT_WRITABLE, $dir);

        // If all is ok - return directory name, as a proof
        return $dir;
    }
    
    /**
     * Get the CKFinder absolute upload path
     * 
     * @return string
     */
    public function ckup() {
        return DOC . STD . '/' . Indi::ini('upload')->path . '/' . Indi::ini('ckeditor')->uploadPath .'/';
    }

    /**
     * This function is used to call the url that is located within same host (localhost)
     * It will return the raw http response, but without headers
     *
     * @static
     * @param $url
     * @return string
     */
    public static function lwget($url) {

        // If hostname is not specified within $url, prepend $url with self hostname and PRE constant
        $url = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . PRE . $url;

        // Get request headers, and declare $hrdS variable for collecting stringified headers list
        $hdrA = apache_request_headers(); $hdrS = '';

        // Unset headers, that may (for some unknown-by-me reasons) cause freeze execution
        unset($hdrA['Connection'], $hdrA['Content-Length'], $hdrA['Content-length'], $hdrA['Accept-Encoding']);

        // Build headers list
        foreach ($hdrA as $n => $v) $hdrS .= $n . ': ' . $v . "\r\n";

        // Prepare context options
        $opt = array('http'=> array('method'=> 'GET', 'header'=> $hdrS));
        
        // Append ssl settings
        if ($_SERVER['REQUEST_SCHEME'] == 'https') $opt['ssl'] = array('verify_peer' => false, 'verify_peer_name' => false);

        // Create context, for passing as a third argument within file_get_contents() call
        $ctx = stream_context_create($opt);

        // Write session data and suspend session, so session file, containing serialized session data
        // will be temporarily unlocked, to prevent caused-by-lockness execution freeze
        session_write_close();

        // Get the response from url call
        ob_start(); $raw = file_get_contents($url, false, $ctx); $error = ob_get_clean();

        // Resume session
        session_start();

        // Return $raw response, or error, if it has occured
        return $error ?: $raw;
    }

    /**
     * Send all DELETE queries to an email for debugging
     *
     * @static
     * @return mixed
     */
    public static function mailDELETE() {

        // If no items in Indi_Db::$DELETEQueryA - return
        if (!count(Indi_Db::$DELETEQueryA)) return;

        // If DELETE queries logging is notturned On - return
        if (!Indi::ini('db')->log->DELETE) return;

        // General info
        $msg = 'Datetime: ' . date('Y-m-d H:i:s') . '<br>';
        $msg .= 'URI: ' . URI . '<br>';
        $msg .= 'Admin: ' . Indi::admin()->title . '<br>';
        $msg .= 'User: ' . Indi::user()->title . '<br><br>';

        // DELETE queries
        foreach (Indi_Db::$DELETEQueryA as $i => $DELETEQueryI)
            $msg .= '#' . ($i + 1)
                . '-' . ($DELETEQueryI['affected'] === false ? 'false' : $DELETEQueryI['affected'] + '') . ': '
                . nl2br($DELETEQueryI['sql']) . '<br>';

        // Separator
        $msg .= '--------------------------------------<br><br>';

        // Empty
        Indi_Db::$DELETEQueryA = array();

        // Mail
        @mail('indi.engine@gmail.com', 'DELETE query at ' . $_SERVER['HTTP_HOST'], $msg, 'Content-Type: text/html; charset=utf-8');

        // If mailing failed - write to special DELETE.log file
        i(str_replace(ar('<br>,<br/>,<br />'), "\n", $msg), 'a', 'log/DELETE.log');
    }

    /**
     * Get a value by a given $key from `config` db table
     *
     * @static
     * @param $key
     * @return mixed
     */
    public static function cfg($key) {
        return Indi::model('Config')->fetchRow('`alias` = "' . $key . '"')->currentValue;
    }

    /**
     * Toggle on/off implicit flushing
     *
     * @static
     * @param $flag bool
     */
    public static function iflush($flag) {

        // Set up headers
        if ($flag && !headers_sent()) {
            header('Cache-Control: no-cache');
            header('X-Accel-Buffering: no');
        }

        // Set up output buffering implicit flush mode
        ob_implicit_flush($flag);

        // Flush
        if ($flag) ob_end_flush();
    }

    /**
     * Prepend every selector found within css file, located at given $cssFile filepath definition,
     * with given $wrapWithSelector, for for example if one of css rules is:
     *
     *      strong {font-size: 100%;}
     *
     * and $wrapWithSelector arg is '.extjs', then the above rule will be changed to
     *
     *      .extjs strong {font-size: 100%;}
     *
     * Css contents with all selector prefixed will be saved in a separate file, having name as original
     * but with postfix given by $cssFilenamePostfix arg.
     *
     * This function is currently used to prevent ExtJS styles to conflict with any other other 3rd-party styles,
     * this happens in case when ExtJS app is injected into ordinary website page
     *
     * @param $cssFile
     * @param string $wrapWithSelector
     * @param string $cssFilenamePostfix
     */
    public function wrapCss($cssFile, $wrapWithSelector = '[i-load]', $cssFilenamePostfix = '_prefixed') {

        // This may take a time
        set_time_limit(0);

        // If $cssFile arg does not contain a valid absolute path
        if ($cssFile != str_replace('\\', '/', realpath($cssFile))) {

            // Find css-file absolute path
            foreach (ar('www,coref,core') as $rep)
                if (is_file($abs_ = DOC . '/' . $rep .  $cssFile) && $abs = $abs_)
                    break;

            // If $abs is still not defined
            if (!$abs) iexit('Given file ' . $cssFile . ' is not a file');

        // Else we assume that $cssFile arg is an absolute path
        } else if (!(($abs_ = realpath($cssFile)) && $abs = $abs_)) iexit('Given file ' . $cssFile . ' is not a file');

        // Get css filepath info
        $info = pathinfo($abs);

        // Check that given file is a css file
        if ($info['extension'] != 'css') iexit('Given file ' . $cssFile . ' is not a css file');

        // Build absolute filename for file, what will be used for containing prefixed version of original css file contents
        $abs_prefixed = $info['dirname'] . '/' . $info['filename'] . $cssFilenamePostfix . '.' . $info['extension'];

        // Get raw contents of css-file
        $raw = file_get_contents($abs);

        // Start implicit flushing
        Indi::iflush(true);

        // Explode css
        $rawA = explode('/* i-style-splitter */', $raw); $outA = array();

        // Info message
        d('Css parts: ' . count($rawA));

        if (count($rawA) < (strlen($raw) / (50 * 1024)))
            iexit('Css file <span style="font-weight: bold;">' . $abs . '</span> is too large to be processed with '
                . 'Sabberworm CSS Parser. Please split that css file\'s contents by inserting '
                . '<span style="font-weight: bold;">/* i-style-splitter * /</span> '
                . 'after each 1000 (approximately) lines of css-rules code');

        // Foreach part of raw css
        foreach ($rawA as $i => $raw) {

            // Info message
            mt(); d('Processing part: ' . ($i + 1) . '...');

            // Strip comments
            $raw = preg_replace('!/\*.*?\*/!s', '', $raw);

            // Init parser and parse
            $parserO = new Sabberworm\CSS\Parser($raw); $rawO = $parserO->parse();

            foreach($rawO->getAllDeclarationBlocks() as $blockO)
                foreach($blockO->getSelectors() as $selectorO)
                    if (!preg_match('/^\.x-(boundlist|layer|css-shadow)/', $s = ltrim($selectorO->getSelector())))
                        $selectorO->setSelector($wrapWithSelector . ' '. $selectorO->getSelector());

            // Get raw css contents having every selector prepended with $prepend
            $outA[] = $raw = $rawO->render();

            // Info message
            d('Processed part: ' . ($i +1) . ', time taken: ' . mt());
        }

        // Write safe css into a file
        file_put_contents($abs_prefixed, im($outA, "\n"));

        // End implicit flushing
        Indi::iflush(false);
    }

    /**
     * Get current modes of all suspicious events or of a certain event, specified by $type arg,
     * or set mode of a certain suspicious event
     *
     * @static
     * @param string $type
     * @param bool $flag
     * @return array|null|bool
     */
    public static function logging($type = null, $flag = null) {

        // If no arguments given - return current state
        if (func_num_args() == 0) return self::$_logging;

        // If $type arg is not a string - return
        if (!is_string($type) || !in_array($type, array_keys(self::$_logging))) return null;

        // If only $type arg is given - return whether or not logging of events of such a type is turned On
        if (func_num_args() == 1) return self::$_logging[$type];

        // If $flag arg is not boolean - return null
        //if (!is_bool($flag)) return null;

        // Assign $flag as a value for item within self::$_log array, under $type key, and return it
        return self::$_logging[$type] = $flag;
    }

    /**
     * @static
     * @param $type
     * @param $data
     * @param string|bool $mail
     */
    public static function log($type, $data, $mail = true) {

        // General info
        $msg = 'Datetime: ' . date('Y-m-d H:i:s') . '<br>';
        $msg .= 'URI: ' . URI . '<br>';
        $msg .= 'Remote IP: ' . $_SERVER['REMOTE_ADDR'] . '<br>';

        // Who?
        if (Indi::admin()->id) $msg .= 'Admin [id#' . Indi::admin()->id . ']: ' . Indi::admin()->title . '<br>';
        if (Indi::user()->id) $msg .= 'User [id#' . Indi::user()->id . ']: ' . Indi::user()->title . '<br>';

        // Spacer, data and separator
        $msg .= '<br>' . print_r($data, true) . '<br>--------------------------------------<br><br>';

        // Mail
        if ($mail) {

            // If where was some input
            if ($input = file_get_contents('php://input')) {

                // Append it to $msg
                $msg .= 'Input data:' . '<br>';
                $msg .= '<br>' . print_r($input, true) . '<br>--------------------------------------<br><br>';
            }

            // If $mail arg is not a valid email address, use 'indi.engine@gmail.com'
            $mail = Indi::rexm('email', $mail) ? $mail : 'indi.engine@gmail.com';

            // Check if Indi::logging($type) contains additional email addresses
            if (is_string(Indi::logging($type)))
                foreach(ar(Indi::logging($type)) as $ccI)
                    if (Indi::rexm('email', $ccI))
                        $mail .= ',' . $ccI;

            // Send mail
            @mail($mail, $type . ' happened at ' . $_SERVER['HTTP_HOST'], $msg, 'Content-Type: text/html; charset=utf-8');
        }

        // If mailing failed - write to special *.log file
        i(str_replace(ar('<br>,<br/>,<br />'), "\n", $msg), 'a', 'log/' . $type . '.log');
    }

    /**
     * Convert format options, compatible with date() function to options, compatible with strftime() function
     *
     * @param $format
     * @return string
     */
    public static function date2strftime($format) {

        // Check for Windows to find and replace the %e modifier correctly
        Indi::$date2strftime['j'] = strtoupper(substr(PHP_OS, 0, 3)) == 'WIN' ? '%#d' : '%e';

        // Convert format
        return preg_replace_callback('/(' . implode('|', array_keys(Indi::$date2strftime)) .  ')/', function($m){
            return Indi::$date2strftime[$m[1]];
        }, $format);
    }

    /**
     * Create and return a new instance of PHPMailer class,
     * pre-configured with ->isHTML(true) and ->CharSet = 'UTF-8'
     *
     * @return PHPMailer
     */
    public static function mailer() {
        $mail = new PHPMailer();
        $mail->isHTML(true);
        $mail->CharSet = 'UTF-8';
        if ($fe = Indi::ini('mail')->default->from->email) $mail->From = $fe;
        if ($fn = Indi::ini('mail')->default->from->name)  $mail->FromName = $fn;
        return $mail;
    }

    /**
     * Get session data, related to current user
     *
     * @static
     * @param string $prop
     * @return array|PHPSTORM_HELPERS\object
     */
    public static function me($prop = null) {

        // If session was not yet started
        if (!session_id()) {

            // Set cookie domain
            Indi::uri()->setCookieDomain();

            // Start session
            session_start();
        }

        // Get session data, containing info about current logged-in admin
        $me = (object) $_SESSION['admin'];

        // If $mode args is explicitly given return session data, stored under $mode key within $_SESSION
        return is_string($prop) ? $me->$prop : $me;
    }

    /**
     * Detect absolute filepath for a relative one, checking
     * 'www', 'coref' and 'core' folders as places of possible location
     *
     * @static
     * @param $src
     * @return string
     */
    public static function abs($src) {
        foreach (ar('www,coref,core') as $rep)
            if (file_exists($abs = DOC . STD . '/' . $rep . $src))
                return $abs;
    }

    /**
     * Send websockets message via client socket, that will be auto-created if not yet exists
     * If $data arg is `false` - socket client will be closed
     */
    public static function ws($data) {

        // If websockets is not enabled - return
        if (!Indi::ini('ws')->enabled) return;

        // If data type is 'realtime' or 'F5', and rabbitmq is enabled
        if (in($data['type'], 'realtime,F5') && Indi::ini('rabbitmq')->enabled) {

            // If rabbitmq connection channel not yet exists
            if (!self::$_mq) {

                // Get credentials
                $mq = (array) Indi::ini('rabbitmq');

                // Create connection
                $connection = new AMQPStreamConnection($mq['host'], $mq['port'], $mq['user'], $mq['pass']);

                // Create channel
                self::$_mq = $connection->channel();
            }

            // Send message
            self::$_mq->basic_publish(new AMQPMessage(json_encode($data)), '', $data['to']);

            // Return
            return;
        }

        // If client socket is not yet created
        if (!self::$_ws && $data !== false)  {

            // Build path
            $path = str_pad(rand(0, 999), 3, '0', STR_PAD_LEFT) .'/' . grs(8) . '/websocket';

            // Protocol
            $prot = is_file(DOC . STD . '/core/application/ws.pem') ? 'wss' : 'ws';

            // Try create client
            try {

                // Create client
                self::$_ws = new WsClient($prot . '://' . Indi::ini('ws')->socket . ':' . Indi::ini('ws')->port . '/' . $path);

            // If exception caught
            } catch (Exception $e) {

                // Log it
                wslog($e->getMessage());
            }
        }

        // If client socket exists
        if (self::$_ws) {

            // If $data arg is `false`
            if ($data === false) {

                // Close channel
                self::$_ws->close();

                // Unset channel
                self::$_ws = null;

            // Else
            } else {

                // Log message
                if (Indi::ini('ws')->log) wsmsglog($data, $data['row'] . '.evt');

                // Send message
                self::$_ws->send(json_encode($data));
            }
        }
    }

    /**
     * Create and return a new instance of Indi_Schedule class
     *
     * @static
     * @param $since
     * @param null $until
     * @param string $gap
     * @return Indi_Schedule
     */
    public static function schedule($since, $until = null, $gap = '') {
        return new Indi_Schedule($since, $until, $gap);
    }
    
    /**
     * Prevent user from doing something when demo-mode is turned On
     */
    public static function demo($flush = true) {
        if ((Indi::ini('general')->demo && Indi::admin()->profileId != 1)
            || (Indi::admin() && (Indi::admin()->demo == 'y' || Indi::admin()->foreign('profileId')->demo == 'y')))
            return $flush ? jflush(false, I_DEMO_ACTION_OFF) : true;
    }

    /**
     * Dispatch /admin/cmd/<method>/ asynchronously,
     * e.g. there will be a call of Admin_CmdController->{$method . 'Action'}($args)
     *
     * @static
     * @param $method
     * @param array $args
     */
    public static function cmd($method, $args = array()) {

        // Default temp dir
        $dir = sys_get_temp_dir();
        
        // If open_basedir restriction is in effect - try to find tmp dir there
        if ($dirS = ini_get('open_basedir'))
            if ($dirA = explode(':', $dirS))
                foreach ($dirA as $dirI)
                    if (preg_match('~te?mp$~', $dirI))
                        $dir = $dirI;

        // Create temporary file
        $env = tempnam($dir, 'cmd');

        // Prepare command
        $cmd = Indi::ini('general')->phpdir . "php ../core/application/cmd.php $method \"$env\"";

        // Fill temporary file with current state
        file_put_contents($env, json_encode(array(
            '_SERVER' => $_SERVER,
            '_COOKIE' => $_COOKIE,
            'args' => $args
        )));

        // If OS is Windows - start new process using 'start' command
        if (Indi::rexm('/^WIN/i', PHP_OS)) pclose(popen('start /B ' . $cmd, 'r'));

        // Else use 'exec' fn
        else exec($cmd . ' > /dev/null &');
    }
}