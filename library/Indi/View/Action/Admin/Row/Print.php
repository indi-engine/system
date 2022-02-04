<?php
class Indi_View_Action_Admin_Row_Print extends Indi_View_Action_Admin_Row {

    /**
     * Replacements array
     *
     * This may be useful for cases when document initial template is in *.docx,
     * format having 'vMyParam1', 'vMyParam2' etc. strings, that are kept after *.docx saved as *.htm
     *
     * Also, conditional templating is supported. To use, you must create a comment/annotation
     * for some text selection, and use 'vMyParam1' as comment's value, so text selection will be kept/stripped
     * depending on whether $this->replace['myParam1'] == true
     *
     * Example: [
     *     'param1' => 'value1,
     *     'param2' => ['value2', 20] // Pad right with '_'-signs up to 20-chars length
     * ]
     *
     * @var array
     */
    public $replace = [];

    /**
     * @return string
     */
    public function render() {

        // Check comments for conditional templating
        if (preg_match("~<div style='mso-element:comment-list'><!\[if !supportAnnotations\]>(.*)?<\/div>.*(?=<\/body>)~s", $this->plain, $clb)) {

            // Find comments
            if (preg_match_all('~<p class=MsoCommentText><span class=MsoCommentReference>.*?</span></p>~s', $this->plain, $m))

                // Foreach comment
                foreach ($m[0] as $expr) {

                    // Check whether comment text does not look like 'vMyParam', and if yes - skip
                    if (!preg_match('~\[w([0-9]+)\]v([A-Z0-9][a-zA-Z0-9]+)~s', strip_tags($expr), $m1)) continue;

                    // Prepare regexp snap points
                    $rex = [
                        '<span[^>]+class=MsoCommentReference.*? id="_anchor_' . ($idx = $m1[1]) . '"',
                        'mso-special-character:comment\'>&nbsp;</span></span></span>'
                    ];

                    // If template variable is not empty/zero/unset/etc
                    if (!$this->replace[lcfirst($key = $m1[2])])

                        // Move strip start position to be earlier
                        array_unshift($rex, '<a style=\'mso-comment-reference:w_' . $idx . ';');

                    // Prepare regexp
                    $_rex = '~' . im($rex, '[^\~]*?') . '~sui';

                    // Strip
                    $this->plain = preg_replace($_rex, '', $this->plain);
            }

            // Remove bottom comments list
            $this->plain = str_replace($clb[0], '', $this->plain);

            // Remove '[w1]' links
            // $this->plain = preg_replace('~\[w[0-9]+\]~', '', $this->plain);
        }

        // Replace static variables
        foreach ($this->replace as $key => $value) {

            // Get replacement with/without placeholder-padding
            if (is_array($value)) $value = $value[0] . str_repeat('&nbsp; ', max(($value[1] - mb_strlen($value[0], 'utf-8')), 0));

            // Replace
            $this->plain = str_replace('v' . ucfirst($key), $value, $this->plain);
        }

        // Set width
        if ($this->replace) $this->plain
            = preg_replace('~(<style[^>]*>)~', '$1body {width: 542pt; margin: 30px auto;} @media print { body {margin: 0 auto;}}', $this->plain);

        // Start output buffering
        ob_start();

        // Push rendered printable contents into special storage, accessible for javascript
        t()->row->view('#print', $this->plain);

        // Return buffered output with parent's return-value
        return ob_get_clean() . parent::render();
    }
}