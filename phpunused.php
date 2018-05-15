<?php

/**
 * Find potential unreferenced PHP files in the code base.
 */

/**
 * Main function for program.
 *
 * @todo Should parse input. Directory parameter?
 *
 * @return void
 */
function main()
{
    $filelist = get_files(".", 'php');

    echo "Potential unreferenced files:\n";
    foreach ($filelist as $fullpath => $name) {
        $grep = grep_php_is_match(".", $name, 'php');
        if (!$grep) {
            echo "Did not find reference to {$fullpath}\n";
        }
    }
    echo "\n";

    echo "Potential unreferenced functions:\n";
    $functionlist = array();
    foreach ($filelist as $fullpath => $name) {
        $functionlist = array_merge($functionlist, get_functions($fullpath));
    }

    foreach ($functionlist as $function) {
        $matches = grep_get_matches(".", $function['name'], 'php');
        if (count($matches) < 2) {
            echo "Did not find reference to {$function['name']}\n";
        }
    }
}

/**
 * Find all php files in code base
 *
 * Does a simple recursive search filtering on file extensions. Returns an array
 * of file names keyed on full path to file.
 *
 * @param string $directory  Directory to search
 * @param string $extensionfilter  File extension to filter on
 */
function get_files($directory, $extensionfilter = null)
{
    $filelist = array();
    if ($handle = opendir($directory)) {
        while (false !== ($entry = readdir($handle))) {
            $entry_fullpath = $directory.DIRECTORY_SEPARATOR . $entry;
            if (is_dir($entry_fullpath)) {
                if ($entry !="." && $entry != "..") {
                    $filelist = array_merge($filelist, get_files($entry_fullpath, $extensionfilter));
                }
            } else {
                if (empty($extensionfilter) || endsWith($entry, ".{$extensionfilter}")) {
                    $filelist[$entry_fullpath] = $entry;
                }
            }
        }
        closedir($handle);
    }
    return $filelist;
}

/**
 * Checks if any files contains a string
 *
 * Filters files on extension. Uses exec().
 *
 * @param string $directory Directory to search
 * @param string $needle String to search for
 */
function grep_php_is_match($directory, $needle, $extension = null)
{
    $output = array();
    $return_var = null;
    $extensionfilter = ($extension ? "--include \\*.{$extension}" : "");
    exec("grep -R -m 1 {$extensionfilter} \"{$needle}\" {$directory}", $output, $return_var);
    return (count($output) ? true : false);
}

/**
 * Checks for files containing a string
 *
 * Filters files on extension. Uses exec().
 *
 * @param string $directory Directory to search
 * @param string $needle String to search for
 */
function grep_get_matches($directory, $needle, $extension = null)
{
    $output = array();
    $return_var = null;
    $extensionfilter = ($extension ? "--include \\*.{$extension}" : "");
    exec("grep -Rn {$extensionfilter} \"{$needle}\" {$directory}", $output, $return_var);
    $out = array();
    foreach ($output as $outline) {
        $out[] = explode(":", $outline);
    }
    return $out;
}

/**
 * Checks if a string starts with another string
 *
 * @param string $haystack The string to search within
 * @param string $needle The string to search for
 */
function startsWith($haystack, $needle)
{
     $length = strlen($needle);
     return (substr($haystack, 0, $length) === $needle);
}

/**
 * Checks if a string ends with another string
 * 
 * @param string $haystack The string to search within
 * @param string $needle The string to search for
 */
function endsWith($haystack, $needle)
{
    $length = strlen($needle);
    return $length === 0 || (substr($haystack, -$length) === $needle);
}

/**
 * Get all functions in a PHP file
 *
 * @param string $file Name of file to parse
 * @return array an array of functions. Each functions is an array of filename, linenumber, functionname
 */
function get_functions($file)
{
    $functions = array();
    $tokens = token_get_all(file_get_contents($file));
    foreach ($tokens as $key => $token) {
        if (is_array($token) && $token[0] == T_FUNCTION &&
        isset($tokens[$key+1]) && is_array($tokens[$key+1]) && $tokens[$key+1][0] == T_WHITESPACE &&
        isset($tokens[$key+2]) && is_array($tokens[$key+2]) && $tokens[$key+2][0] == T_STRING
        ) {
            $functions[] = array('file' => $file, 'line' => $tokens[$key+2][2], 'name' => $tokens[$key+2][1]);
        }
    }
    return $functions;
}

main();
