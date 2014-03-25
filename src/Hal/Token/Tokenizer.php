<?php

/*
 * (c) Jean-François Lépine <https://twitter.com/Halleck45>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hal\Token;

/**
 * Tokenize file
 *
 * @author Jean-François Lépine <https://twitter.com/Halleck45>
 */
class Tokenizer {

    /**
     * Tokenize file
     *
     * @param $filename
     * @return array
     */
    public function tokenize($filename) {

        //
        // check validity of file
        if(1 === version_compare('5.0.4', PHP_VERSION)) {
            if(!\php_check_syntax($filename)) {
                return array();
            }
        } else {
            $output = shell_exec(sprintf('php -l %s', escapeshellarg($filename)));
            if(!preg_match('!No syntax errors detected!', $output)) {
                return array();
            }
        }

        //
        // fixes memory problems with large files
        // https://github.com/Halleck45/PhpMetrics/issues/13
        $size = filesize($filename);
        $limit = 102400; // around 100 Ko
        if($size > $limit) {
            $tokens = array();
            $hwnd = fopen($filename, 'r');
            while (!feof($hwnd)) {
                $content = stream_get_line($hwnd, $limit);
                // string is arbitrary splitted, so content can be incorrect
                // for example: "Unterminated comment starting..."
                $content .= '/* */';
                $tokens = array_merge($tokens, token_get_all($content));
                unset($content);
            }
            return $tokens;
        }

        return token_get_all(file_get_contents($filename));
    }

}