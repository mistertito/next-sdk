<?php
/**
 * XLite_Sniffs_CSS_DuplicateClassDefinitionSniff.
 *
 * PHP version 5
 *
 * @category  PHP
 * @package   PHP_CodeSniffer
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @copyright 2006 Squiz Pty Ltd (ABN 77 084 670 600)
 * @license   http://matrix.squiz.net/developer/tools/php_cs/licence BSD Licence
 * @version   CVS: $Id$
 * @link      http://pear.php.net/package/PHP_CodeSniffer
 */

/**
 * XLite_Sniffs_CSS_DuplicateClassDefinitionSniff.
 *
 * Check for duplicate class definitions that can be merged into one.
 *
 * @category  PHP
 * @package   PHP_CodeSniffer
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @copyright 2006 Squiz Pty Ltd (ABN 77 084 670 600)
 * @license   http://matrix.squiz.net/developer/tools/php_cs/licence BSD Licence
 * @version   Release: @package_version@
 * @link      http://pear.php.net/package/PHP_CodeSniffer
 */
class XLite_Sniffs_CSS_DuplicateClassDefinitionSniff extends XLite_ReqCodesSniff
{

    /**
     * A list of tokenizers this sniff supports.
     *
     * @var array
     */
    public $supportedTokenizers = array('CSS');


    /**
     * Returns the token types that this sniff is interested in.
     *
     * @return array(int)
     */
    public function register()
    {
        return array(T_OPEN_TAG);

    }//end register()


    /**
     * Processes the tokens that this sniff is interested in.
     *
     * @param PHP_CodeSniffer_File $phpcsFile The file where the token was found.
     * @param int                  $stackPtr  The position in the stack where
     *                                        the token was found.
     *
     * @return void
     */
    public function process(PHP_CodeSniffer_File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();

        // Find the content of each class definition name.
        $next = $phpcsFile->findNext(T_OPEN_CURLY_BRACKET, ($stackPtr + 1));
        if ($next === false) {
            // No class definitions in the file.
            return;
        }

        $find = array(
                 T_CLOSE_CURLY_BRACKET,
                 T_COMMENT,
                 T_OPEN_TAG,
                );

        while ($next !== false) {
            $prev = $phpcsFile->findPrevious($find, ($next - 1));

            // Create a sorted name for the class so we can compare classes
            // even when the individual names are all over the place.
            $name = '';
            for ($i = ($prev + 1); $i < $next; $i++) {
                $name .= $tokens[$i]['content'];
            }

            $name = trim($name);
            $name = str_replace("\n", ' ', $name);
            $name = preg_replace('|[\s]+|', ' ', $name);
            $name = str_replace(', ', ',', $name);

            $names = explode(',', $name);
            sort($names);
            $name = implode(',', $names);

            if (isset($classNames[$name]) === true) {
                $first = $classNames[$name];
                $line  = $tokens[$first]['line'];
                $error = "Duplicate class definition found; first defined on line $line";
                $phpcsFile->addError($this->getReqPrefix('REQ.CSS.1.0.5') . $error, $next);
            } else {
                $classNames[$name] = $next;
            }

            $next = $phpcsFile->findNext(T_OPEN_CURLY_BRACKET, ($next + 1));
        }//end while

    }//end process()


}//end class
?>
