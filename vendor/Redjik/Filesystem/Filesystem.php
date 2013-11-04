<?php
/**
 * @author Ivan Matveev <Redjiks@gmail.com>.
 */

namespace Redjik\Filesystem;
use Redjik\Filesystem\Exception\DirectoryNotEmptyException;
use Redjik\Filesystem\Exception\DoesNotExistException;
use Redjik\Filesystem\Exception\Exception;
use Redjik\Filesystem\Exception\ExistException;
use Redjik\Filesystem\Exception\IsDirectoryException;
use Redjik\Filesystem\Exception\NotADirectoryException;
use Redjik\Filesystem\Exception\NotAStreamException;
use Redjik\Filesystem\Exception\PermissionException;
use Redjik\Filesystem\Exception\StatException;


/**
 * Class Filesystem
 * @package Redjik\Filesystem
 */
class Filesystem
{

    /**
     * Unified method to unlink file or remove directory
     * Can remove recursively with $recursive option set to true
     *
     * @param string $dirOrFileName
     * @param bool $recursive
     *
     * @throws PermissionException
     * @throws DoesNotExistException
     * @throws DirectoryNotEmptyException
     *
     * @return bool
     */
    public function purge($dirOrFileName,$recursive = false)
    {
        try{
            $result = $this->unlink($dirOrFileName);
        }catch (IsDirectoryException $e){
            try{
                $result = $this->rmdir($dirOrFileName);
            }catch (DirectoryNotEmptyException $e){
                if ($recursive){
                    $files = array_diff($this->scandir($dirOrFileName), array('.','..'));
                    foreach ($files as $file)
                    {
                        $this->purge($dirOrFileName.DIRECTORY_SEPARATOR.$file,true);
                    }
                    $result = $this->rmdir($dirOrFileName);
                }else{
                    throw new DirectoryNotEmptyException($e->getMessage(),$e->getCode());
                }
            }
        }

        return $result;
    }

    /**
     * file_put_contents wrapper
     * saves string array or resource to file
     * creates directory if doesn't exist
     * @see file_put_contents
     *
     * @param $filename
     * @param $data
     * @param null $flags
     * @param null $context
     * @return int
     */
    public function saveToFile($filename, $data, $flags = null, $context = null)
    {
        try{
            $result = $this->file_put_contents($filename,$data,$flags, $context);
        }catch (DoesNotExistException $e){
            $dir = dirname($filename).DIRECTORY_SEPARATOR;
            $this->mkdir($dir,0775,true);
            $result = $this->file_put_contents($filename,$data,$flags, $context);
        }

        return $result;
    }

    /**
     * (PHP 4, PHP 5)<br/>
     * Changes file group
     * @link http://php.net/manual/en/function.chgrp.php
     * @param string $filename <p>
     * Path to the file.
     * </p>
     * @param mixed $group <p>
     * A group name or number.
     * </p>
     *
     * @throws DoesNotExistException
     * @throws PermissionException
     * @throws NotADirectoryException
     *
     * @return bool true on success or false on failure.
     */
    public function chgrp($filename, $group)
    {
        return $this->runWithErrorHandler('chgrp',func_get_args());
    }

    /**
     * (PHP 4, PHP 5)<br/>
     * Changes file mode
     * @link http://php.net/manual/en/function.chmod.php
     * @param string $filename <p>
     * Path to the file.
     * </p>
     * @param int $mode <p>
     * Note that mode is not automatically
     * assumed to be an octal value, so strings (such as "g+w") will
     * not work properly. To ensure the expected operation,
     * you need to prefix mode with a zero (0):
     * </p>
     * <p>
     * ]]>
     * </p>
     * <p>
     * The mode parameter consists of three octal
     * number components specifying access restrictions for the owner,
     * the user group in which the owner is in, and to everybody else in
     * this order. One component can be computed by adding up the needed
     * permissions for that target user base. Number 1 means that you
     * grant execute rights, number 2 means that you make the file
     * writeable, number 4 means that you make the file readable. Add
     * up these numbers to specify needed rights. You can also read more
     * about modes on Unix systems with 'man 1 chmod'
     * and 'man 2 chmod'.
     * </p>
     * <p>
     *
     * @throws DoesNotExistException
     * @throws PermissionException
     * @throws NotADirectoryException
     *
     * @return bool true on success or false on failure.
     */
    public function chmod($filename, $mode)
    {
        return $this->runWithErrorHandler('chmod',func_get_args());
    }

    /**
     * (PHP 4, PHP 5)<br/>
     * Changes file owner
     * @link http://php.net/manual/en/function.chown.php
     * @param string $filename <p>
     * Path to the file.
     * </p>
     * @param mixed $user <p>
     * A user name or number.
     * </p>
     *
     * @throws DoesNotExistException
     * @throws PermissionException
     * @throws NotADirectoryException
     *
     * @return bool true on success or false on failure.
     */
    public function chown($filename, $user)
    {
        return $this->runWithErrorHandler('chown',func_get_args());
    }

    /**
     * (PHP 4, PHP 5)<br/>
     * Copies file
     * @link http://php.net/manual/en/function.copy.php
     * @param string $source <p>
     * Path to the source file.
     * </p>
     * @param string $dest <p>
     * The destination path. If dest is a URL, the
     * copy operation may fail if the wrapper does not support overwriting of
     * existing files.
     * </p>
     * <p>
     * If the destination file already exists, it will be overwritten.
     * </p>
     * @param resource $context [optional] <p>
     * A valid context resource created with
     * stream_context_create.
     * </p>
     *
     * @throws DoesNotExistException
     * @throws PermissionException
     * @throws IsDirectoryException
     *
     * @return bool true on success or false on failure.
     */
    public function copy($source, $dest, $context = null)
    {
        return $this->runWithErrorHandler('copy',func_get_args());
    }



    /**
     * (PHP 4, PHP 5)<br/>
     * Closes an open file pointer
     * @link http://php.net/manual/en/function.fclose.php
     * @param resource $handle <p>
     * The file pointer must be valid, and must point to a file successfully
     * opened by fopen or fsockopen.
     * </p>
     *
     * @throws NotAStreamException
     *
     * @return bool true on success or false on failure.
     */
    public function fclose($handle)
    {
        return $this->runWithErrorHandler('fclose',func_get_args());
    }

    /**
     * (PHP 4, PHP 5)<br/>
     * Opens file or URL
     * @link http://php.net/manual/en/function.fopen.php
     * @param string $filename <p>
     * If filename is of the form "scheme://...", it
     * is assumed to be a URL and PHP will search for a protocol handler
     * (also known as a wrapper) for that scheme. If no wrappers for that
     * protocol are registered, PHP will emit a notice to help you track
     * potential problems in your script and then continue as though
     * filename specifies a regular file.
     * </p>
     * <p>
     * If PHP has decided that filename specifies
     * a local file, then it will try to open a stream on that file.
     * The file must be accessible to PHP, so you need to ensure that
     * the file access permissions allow this access.
     * If you have enabled &safemode;,
     * or open_basedir further
     * restrictions may apply.
     * </p>
     * <p>
     * If PHP has decided that filename specifies
     * a registered protocol, and that protocol is registered as a
     * network URL, PHP will check to make sure that
     * allow_url_fopen is
     * enabled. If it is switched off, PHP will emit a warning and
     * the fopen call will fail.
     * </p>
     * <p>
     * The list of supported protocols can be found in . Some protocols (also referred to as
     * wrappers) support context
     * and/or &php.ini; options. Refer to the specific page for the
     * protocol in use for a list of options which can be set. (e.g.
     * &php.ini; value user_agent used by the
     * http wrapper).
     * </p>
     * <p>
     * On the Windows platform, be careful to escape any backslashes
     * used in the path to the file, or use forward slashes.
     * ]]>
     * </p>
     * @param string $mode <p>
     * The mode parameter specifies the type of access
     * you require to the stream. It may be any of the following:
     * <table>
     * A list of possible modes for fopen
     * using mode
     * <tr valign="top">
     * <td>mode</td>
     * <td>Description</td>
     * </tr>
     * <tr valign="top">
     * <td>'r'</td>
     * <td>
     * Open for reading only; place the file pointer at the
     * beginning of the file.
     * </td>
     * </tr>
     * <tr valign="top">
     * <td>'r+'</td>
     * <td>
     * Open for reading and writing; place the file pointer at
     * the beginning of the file.
     * </td>
     * </tr>
     * <tr valign="top">
     * <td>'w'</td>
     * <td>
     * Open for writing only; place the file pointer at the
     * beginning of the file and truncate the file to zero length.
     * If the file does not exist, attempt to create it.
     * </td>
     * </tr>
     * <tr valign="top">
     * <td>'w+'</td>
     * <td>
     * Open for reading and writing; place the file pointer at
     * the beginning of the file and truncate the file to zero
     * length. If the file does not exist, attempt to create it.
     * </td>
     * </tr>
     * <tr valign="top">
     * <td>'a'</td>
     * <td>
     * Open for writing only; place the file pointer at the end of
     * the file. If the file does not exist, attempt to create it.
     * </td>
     * </tr>
     * <tr valign="top">
     * <td>'a+'</td>
     * <td>
     * Open for reading and writing; place the file pointer at
     * the end of the file. If the file does not exist, attempt to
     * create it.
     * </td>
     * </tr>
     * <tr valign="top">
     * <td>'x'</td>
     * <td>
     * Create and open for writing only; place the file pointer at the
     * beginning of the file. If the file already exists, the
     * fopen call will fail by returning false and
     * generating an error of level E_WARNING. If
     * the file does not exist, attempt to create it. This is equivalent
     * to specifying O_EXCL|O_CREAT flags for the
     * underlying open(2) system call.
     * </td>
     * </tr>
     * <tr valign="top">
     * <td>'x+'</td>
     * <td>
     * Create and open for reading and writing; place the file pointer at
     * the beginning of the file. If the file already exists, the
     * fopen call will fail by returning false and
     * generating an error of level E_WARNING. If
     * the file does not exist, attempt to create it. This is equivalent
     * to specifying O_EXCL|O_CREAT flags for the
     * underlying open(2) system call.
     * </td>
     * </tr>
     * </table>
     * </p>
     * <p>
     * Different operating system families have different line-ending
     * conventions. When you write a text file and want to insert a line
     * break, you need to use the correct line-ending character(s) for your
     * operating system. Unix based systems use \n as the
     * line ending character, Windows based systems use \r\n
     * as the line ending characters and Macintosh based systems use
     * \r as the line ending character.
     * </p>
     * <p>
     * If you use the wrong line ending characters when writing your files, you
     * might find that other applications that open those files will "look
     * funny".
     * </p>
     * <p>
     * Windows offers a text-mode translation flag ('t')
     * which will transparently translate \n to
     * \r\n when working with the file. In contrast, you
     * can also use 'b' to force binary mode, which will not
     * translate your data. To use these flags, specify either
     * 'b' or 't' as the last character
     * of the mode parameter.
     * </p>
     * <p>
     * The default translation mode depends on the SAPI and version of PHP that
     * you are using, so you are encouraged to always specify the appropriate
     * flag for portability reasons. You should use the 't'
     * mode if you are working with plain-text files and you use
     * \n to delimit your line endings in your script, but
     * expect your files to be readable with applications such as notepad. You
     * should use the 'b' in all other cases.
     * </p>
     * <p>
     * If you do not specify the 'b' flag when working with binary files, you
     * may experience strange problems with your data, including broken image
     * files and strange problems with \r\n characters.
     * </p>
     * <p>
     * For portability, it is strongly recommended that you always
     * use the 'b' flag when opening files with fopen.
     * </p>
     * <p>
     * Again, for portability, it is also strongly recommended that
     * you re-write code that uses or relies upon the 't'
     * mode so that it uses the correct line endings and
     * 'b' mode instead.
     * </p>
     * @param bool $use_include_path [optional] <p>
     * The optional third use_include_path parameter
     * can be set to '1' or true if you want to search for the file in the
     * include_path, too.
     * </p>
     * @param resource $context [optional] &note.context-support;
     *
     * @throws DoesNotExistException
     * @throws PermissionException
     * @throws IsDirectoryException
     * @throws ExistException
     *
     * @return resource a file pointer resource on success, or false on error.
     */
    public function fopen($filename, $mode, $use_include_path = null, $context = null)
    {
        return $this->runWithErrorHandler('fopen',func_get_args());
    }

    /**
     * (PHP 4, PHP 5)<br/>
     * Tests for end-of-file on a file pointer
     * @link http://php.net/manual/en/function.feof.php
     * @param resource $handle &fs.validfp.all;
     *
     * @throws NotAStreamException
     *
     * @return bool true if the file pointer is at EOF or an error occurs
     * (including socket timeout); otherwise returns false.
     */
    public function feof($handle)
    {
        return $this->runWithErrorHandler('feof',func_get_args());
    }


    /**
     * (PHP 4 &gt;= 4.0.1, PHP 5)<br/>
     * Flushes the output to a file
     * @link http://php.net/manual/en/function.fflush.php
     * @param resource $handle &fs.validfp.all;
     *
     * @throws NotAStreamException
     *
     * @return bool true on success or false on failure.
     */
    public function fflush($handle)
    {
        return $this->runWithErrorHandler('fflush',func_get_args());
    }

    /**
     * (PHP 4, PHP 5)<br/>
     * Gets character from file pointer
     * @link http://php.net/manual/en/function.fgetc.php
     * @param resource $handle &fs.validfp.all;
     *
     * @throws NotAStreamException
     *
     * @return string a string containing a single character read from the file pointed
     * to by handle. Returns false on EOF.
     */
    public function fgetc($handle)
    {
        return $this->runWithErrorHandler('fgetc',func_get_args());
    }

    /**
     * (PHP 4, PHP 5)<br/>
     * Gets line from file pointer and parse for CSV fields
     * @link http://php.net/manual/en/function.fgetcsv.php
     * @param resource $handle <p>
     * A valid file pointer to a file successfully opened by
     * fopen, popen, or
     * fsockopen.
     * </p>
     * @param int $length [optional] <p>
     * Must be greater than the longest line (in characters) to be found in
     * the CSV file (allowing for trailing line-end characters). It became
     * optional in PHP 5. Omitting this parameter (or setting it to 0 in PHP
     * 5.0.4 and later) the maximum line length is not limited, which is
     * slightly slower.
     * </p>
     * @param string $delimiter [optional] <p>
     * Set the field delimiter (one character only).
     * </p>
     * @param string $enclosure [optional] <p>
     * Set the field enclosure character (one character only).
     * </p>
     * @param string $escape [optional] <p>
     * Set the escape character (one character only). Defaults as a backslash.
     * </p>
     *
     * @throws NotAStreamException
     *
     * @return array an indexed array containing the fields read.
     * </p>
     * <p>
     * A blank line in a CSV file will be returned as an array
     * comprising a single null field, and will not be treated
     * as an error.
     * </p>
     * &note.line-endings;
     * <p>
     * fgetcsv returns &null; if an invalid
     * handle is supplied or false on other errors,
     * including end of file.
     */
    public function fgetcsv($handle, $length = null, $delimiter = null, $enclosure = null, $escape = null)
    {
        return $this->runWithErrorHandler('fgetcsv',func_get_args());
    }

    /**
     * (PHP 4, PHP 5)<br/>
     * Gets line from file pointer
     * @link http://php.net/manual/en/function.fgets.php
     * @param resource $handle &fs.validfp.all;
     * @param int $length [optional] <p>
     * Reading ends when length - 1 bytes have been
     * read, on a newline (which is included in the return value), or on EOF
     * (whichever comes first). If no length is specified, it will keep
     * reading from the stream until it reaches the end of the line.
     * </p>
     * <p>
     * Until PHP 4.3.0, omitting it would assume 1024 as the line length.
     * If the majority of the lines in the file are all larger than 8KB,
     * it is more resource efficient for your script to specify the maximum
     * line length.
     * </p>
     *
     * @throws NotAStreamException
     *
     * @return string a string of up to length - 1 bytes read from
     * the file pointed to by handle.
     * </p>
     * <p>
     * If an error occurs, returns false.
     */
    public function fgets($handle, $length = null)
    {
        return $this->runWithErrorHandler('fgets',func_get_args());
    }


    /**
     * (PHP 4, PHP 5)<br/>
     * Gets line from file pointer and strip HTML tags
     * @link http://php.net/manual/en/function.fgetss.php
     * @param resource $handle &fs.validfp.all;
     * @param int $length [optional] <p>
     * Length of the data to be retrieved.
     * </p>
     * @param string $allowable_tags [optional] <p>
     * You can use the optional third parameter to specify tags which should
     * not be stripped.
     * </p>
     *
     * @throws NotAStreamException
     *
     * @return string a string of up to length - 1 bytes read from
     * the file pointed to by handle, with all HTML and PHP
     * code stripped.
     * </p>
     * <p>
     * If an error occurs, returns false.
     */
    public function fgetss($handle, $length = null, $allowable_tags = null)
    {
        return $this->runWithErrorHandler('fgetss',func_get_args());
    }

    /**
     * (PHP 4 &gt;= 4.3.0, PHP 5)<br/>
     * Reads entire file into a string
     * @link http://php.net/manual/en/function.file-get-contents.php
     * @param string $filename <p>
     * Name of the file to read.
     * </p>
     * @param int $flags [optional] <p>
     * Prior to PHP 6, this parameter is called
     * use_include_path and is a bool.
     * As of PHP 5 the FILE_USE_INCLUDE_PATH can be used
     * to trigger include path
     * search.
     * </p>
     * <p>
     * The value of flags can be any combination of
     * the following flags (with some restrictions), joined with the
     * binary OR (|)
     * operator.
     * </p>
     * <p>
     * <table>
     * Available flags
     * <tr valign="top">
     * <td>Flag</td>
     * <td>Description</td>
     * </tr>
     * <tr valign="top">
     * <td>
     * FILE_USE_INCLUDE_PATH
     * </td>
     * <td>
     * Search for filename in the include directory.
     * See include_path for more
     * information.
     * </td>
     * </tr>
     * <tr valign="top">
     * <td>
     * FILE_TEXT
     * </td>
     * <td>
     * As of PHP 6, the default encoding of the read
     * data is UTF-8. You can specify a different encoding by creating a
     * custom context or by changing the default using
     * stream_default_encoding. This flag cannot be
     * used with FILE_BINARY.
     * </td>
     * </tr>
     * <tr valign="top">
     * <td>
     * FILE_BINARY
     * </td>
     * <td>
     * With this flag, the file is read in binary mode. This is the default
     * setting and cannot be used with FILE_TEXT.
     * </td>
     * </tr>
     * </table>
     * </p>
     * @param resource $context [optional] <p>
     * A valid context resource created with
     * stream_context_create. If you don't need to use a
     * custom context, you can skip this parameter by &null;.
     * </p>
     * @param int $offset [optional] <p>
     * The offset where the reading starts.
     * </p>
     * @param int $maxlen [optional] <p>
     * Maximum length of data read. The default is to read until end
     * of file is reached.
     * </p>
     *
     * @throws DoesNotExistException
     * @throws PermissionException
     *
     * @return string The function returns the read data or false on failure.
     */
    public function file_get_contents($filename, $flags = null, $context = null, $offset = null, $maxlen = null)
    {
        return $this->runWithErrorHandler('file_get_contents',func_get_args());
    }

    /**
     * (PHP 5)<br/>
     * Write a string to a file
     * @link http://php.net/manual/en/function.file-put-contents.php
     * @param string $filename <p>
     * Path to the file where to write the data.
     * </p>
     * @param mixed $data <p>
     * The data to write. Can be either a string, an
     * array or a stream resource.
     * </p>
     * <p>
     * If data is a stream resource, the
     * remaining buffer of that stream will be copied to the specified file.
     * This is similar with using stream_copy_to_stream.
     * </p>
     * <p>
     * You can also specify the data parameter as a single
     * dimension array. This is equivalent to
     * file_put_contents($filename, implode('', $array)).
     * </p>
     * @param int $flags [optional] <p>
     * The value of flags can be any combination of
     * the following flags (with some restrictions), joined with the binary OR
     * (|) operator.
     * </p>
     * <p>
     * <table>
     * Available flags
     * <tr valign="top">
     * <td>Flag</td>
     * <td>Description</td>
     * </tr>
     * <tr valign="top">
     * <td>
     * FILE_USE_INCLUDE_PATH
     * </td>
     * <td>
     * Search for filename in the include directory.
     * See include_path for more
     * information.
     * </td>
     * </tr>
     * <tr valign="top">
     * <td>
     * FILE_APPEND
     * </td>
     * <td>
     * If file filename already exists, append
     * the data to the file instead of overwriting it. Mutually
     * exclusive with LOCK_EX since appends are atomic and thus there
     * is no reason to lock.
     * </td>
     * </tr>
     * <tr valign="top">
     * <td>
     * LOCK_EX
     * </td>
     * <td>
     * Acquire an exclusive lock on the file while proceeding to the
     * writing. Mutually exclusive with FILE_APPEND.
     * </td>
     * </tr>
     * <tr valign="top">
     * <td>
     * FILE_TEXT
     * </td>
     * <td>
     * data is written in text mode. If unicode
     * semantics are enabled, the default encoding is UTF-8.
     * You can specify a different encoding by creating a custom context
     * or by using the stream_default_encoding to
     * change the default. This flag cannot be used with
     * FILE_BINARY. This flag is only available since
     * PHP 6.
     * </td>
     * </tr>
     * <tr valign="top">
     * <td>
     * FILE_BINARY
     * </td>
     * <td>
     * data will be written in binary mode. This
     * is the default setting and cannot be used with
     * FILE_TEXT. This flag is only available since
     * PHP 6.
     * </td>
     * </tr>
     * </table>
     * </p>
     * @param resource $context [optional] <p>
     * A valid context resource created with
     * stream_context_create.
     * </p>
     *
     * @throws PermissionException
     * @throws IsDirectoryException
     *
     * @return int The function returns the number of bytes that were written to the file, or
     * false on failure.
     */
    public function file_put_contents($filename, $data, $flags = null, $context = null)
    {
        return $this->runWithErrorHandler('file_put_contents',func_get_args());
    }

    /**
     * (PHP 4, PHP 5)<br/>
     * Reads entire file into an array
     * @link http://php.net/manual/en/function.file.php
     * @param string $filename <p>
     * Path to the file.
     * </p>
     * &tip.fopen-wrapper;
     * @param int $flags [optional] <p>
     * The optional parameter flags can be one, or
     * more, of the following constants:
     * FILE_USE_INCLUDE_PATH
     * Search for the file in the include_path.
     * @param resource $context [optional] <p>
     * A context resource created with the
     * stream_context_create function.
     * </p>
     * <p>
     * &note.context-support;
     * </p>
     *
     * @throws PermissionException
     * @throws DoesNotExistException
     *
     * @return array the file in an array. Each element of the array corresponds to a
     * line in the file, with the newline still attached. Upon failure,
     * file returns false.
     * </p>
     * <p>
     * Each line in the resulting array will include the line ending, unless
     * FILE_IGNORE_NEW_LINES is used, so you still need to
     * use rtrim if you do not want the line ending
     * present.
     */
    public function file($filename, $flags = null, $context = null)
    {
        return $this->runWithErrorHandler('file',func_get_args());
    }

    /**
     * (PHP 4, PHP 5)<br/>
     * Gets last access time of file
     * @link http://php.net/manual/en/function.fileatime.php
     * @param string $filename <p>
     * Path to the file.
     * </p>
     *
     * @throws DoesNotExistException
     * @throws PermissionException
     *
     * @return int the time the file was last accessed, or false on failure.
     * The time is returned as a Unix timestamp.
     */
    public function fileatime($filename)
    {
        return $this->stat($filename)['atime'];
    }

    /**
     * (PHP 4, PHP 5)<br/>
     * Gets inode change time of file
     * @link http://php.net/manual/en/function.filectime.php
     * @param string $filename <p>
     * Path to the file.
     * </p>
     *
     * @throws DoesNotExistException
     * @throws PermissionException
     *
     * @return int the time the file was last changed, or false on failure.
     * The time is returned as a Unix timestamp.
     */
    public function filectime($filename)
    {
        return $this->stat($filename)['ctime'];
    }

    /**
     * (PHP 4, PHP 5)<br/>
     * Gets file group
     * @link http://php.net/manual/en/function.filegroup.php
     * @param string $filename <p>
     * Path to the file.
     * </p>
     *
     * @throws DoesNotExistException
     * @throws PermissionException
     *
     * @return int the group ID of the file, or false in case
     * of an error. The group ID is returned in numerical format, use
     * posix_getgrgid to resolve it to a group name.
     * Upon failure, false is returned.
     */
    public function filegroup($filename)
    {
        return $this->stat($filename)['gid'];
    }

    /**
     * (PHP 4, PHP 5)<br/>
     * Gets file inode
     * @link http://php.net/manual/en/function.fileinode.php
     * @param string $filename <p>
     * Path to the file.
     * </p>
     *
     * @throws DoesNotExistException
     * @throws PermissionException
     *
     * @return int the inode number of the file, or false on failure.
     */
    public function fileinode ($filename)
    {
        return $this->stat($filename)['ino'];
    }

    /**
     * (PHP 4, PHP 5)<br/>
     * Gets file modification time
     * @link http://php.net/manual/en/function.filemtime.php
     * @param string $filename <p>
     * Path to the file.
     * </p>
     *
     * @throws DoesNotExistException
     * @throws PermissionException
     *
     * @return int the time the file was last modified, or false on failure.
     * The time is returned as a Unix timestamp, which is
     * suitable for the date function.
     */
    public function filemtime ($filename)
    {
        return $this->stat($filename)['mtime'];
    }

    /**
     * (PHP 4, PHP 5)<br/>
     * Gets file owner
     * @link http://php.net/manual/en/function.fileowner.php
     * @param string $filename <p>
     * Path to the file.
     * </p>
     *
     * @throws DoesNotExistException
     * @throws PermissionException
     *
     * @return int the user ID of the owner of the file, or false on failure.
     * The user ID is returned in numerical format, use
     * posix_getpwuid to resolve it to a username.
     */
    public function fileowner ($filename)
    {
        return $this->stat($filename)['uid'];
    }

    /**
     * (PHP 4, PHP 5)<br/>
     * Gets file permissions
     * @link http://php.net/manual/en/function.fileperms.php
     * @param string $filename <p>
     * Path to the file.
     * </p>
     *
     * @throws DoesNotExistException
     * @throws PermissionException
     *
     * @return int the permissions on the file, or false on failure.
     */
    public function fileperms ($filename)
    {
        return $this->stat($filename)['mode'];
    }

    /**
     * (PHP 4, PHP 5)<br/>
     * Gets file size
     * @link http://php.net/manual/en/function.filesize.php
     * @param string $filename <p>
     * Path to the file.
     * </p>
     *
     * @throws DoesNotExistException
     * @throws PermissionException
     *
     * @return int the size of the file in bytes, or false (and generates an error
     * of level E_WARNING) in case of an error.
     */
    public function filesize ($filename)
    {
        return $this->stat($filename)['size'];
    }

    /**
     * (PHP 4, PHP 5)<br/>
     * Gets file type
     * @link http://php.net/manual/en/function.filetype.php
     * @param string $filename <p>
     * Path to the file.
     * </p>
     *
     * @throws StatException
     *
     * @return string the type of the file. Possible values are fifo, char,
     * dir, block, link, file, socket and unknown.
     * </p>
     * <p>
     * Returns false if an error occurs. filetype will also
     * produce an E_NOTICE message if the stat call fails
     * or if the file type is unknown.
     */
    public function filetype ($filename)
    {
        return $this->runWithErrorHandler('filetype',func_get_args());
    }


    /**
     * (PHP 4, PHP 5)<br/>
     * Portable advisory file locking
     * @link http://php.net/manual/en/function.flock.php
     * @param resource $handle <p>
     * An open file pointer.
     * </p>
     * @param int $operation <p>
     * operation is one of the following:
     * LOCK_SH to acquire a shared lock (reader).
     * @param int $wouldblock [optional] <p>
     * The optional third argument is set to true if the lock would block
     * (EWOULDBLOCK errno condition).
     * </p>
     *
     * @throws NotAStreamException
     *
     * @return bool true on success or false on failure.
     */
    public function flock($handle, $operation, &$wouldblock = null)
    {
        return $this->runWithErrorHandler('flock',func_get_args());
    }

    /**
     * (PHP 5 &gt;= 5.1.0)<br/>
     * Format line as CSV and write to file pointer
     * @link http://php.net/manual/en/function.fputcsv.php
     * @param resource $handle &fs.validfp.all;
     * @param array $fields <p>
     * An array of values.
     * </p>
     * @param string $delimiter [optional] <p>
     * The optional delimiter parameter sets the field
     * delimiter (one character only).
     * </p>
     * @param string $enclosure [optional] <p>
     * The optional enclosure parameter sets the field
     * enclosure (one character only).
     * </p>
     *
     * @throws NotAStreamException
     *
     * @return int the length of the written string or false on failure.
     */
    public function fputcsv($handle, array $fields, $delimiter = null, $enclosure = null)
    {
        return $this->runWithErrorHandler('fputcsv',func_get_args());
    }

    /**
     * (PHP 4, PHP 5)<br/>
     * &Alias; <function>fwrite</function>
     * @see fwrite()
     * @link http://php.net/manual/en/function.fputs.php
     * Binary-safe file write
     * @param resource $handle &fs.file.pointer;
     * @param string $string <p>
     * The string that is to be written.
     * </p>
     * @param int $length [optional] <p>
     * If the length argument is given, writing will
     * stop after length bytes have been written or
     * the end of string is reached, whichever comes
     * first.
     * </p>
     * <p>
     * Note that if the length argument is given,
     * then the magic_quotes_runtime
     * configuration option will be ignored and no slashes will be
     * stripped from string.
     * </p>
     *
     * @throws NotAStreamException
     *
     * @return int
     */
    public function fputs($handle, $string, $length = null)
    {
        return $this->fwrite($handle, $string, $length);
    }

    /**
     * (PHP 4, PHP 5)<br/>
     * Binary-safe file read
     * @link http://php.net/manual/en/function.fread.php
     * @param resource $handle &fs.file.pointer;
     * @param int $length <p>
     * Up to length number of bytes read.
     * </p>
     *
     * @throws NotAStreamException
     *
     * @return string the read string or false on failure.
     */
    public function fread($handle, $length)
    {
        return $this->runWithErrorHandler('fread',func_get_args());
    }

    /**
     * (PHP 4 &gt;= 4.0.1, PHP 5)<br/>
     * Parses input from a file according to a format
     * @link http://php.net/manual/en/function.fscanf.php
     * @param resource $handle &fs.file.pointer;
     * @param string $format <p>
     * The specified format as described in the
     * sprintf documentation.
     * </p>
     * @return mixed If only two parameters were passed to this function, the values parsed will be
     * returned as an array. Otherwise, if optional parameters are passed, the
     * function will return the number of assigned values. The optional
     * parameters must be passed by reference.
     */
    public function fscanf($handle, $format)
    {
        return $this->runWithErrorHandler('fscanf',func_get_args());
    }

    /**
     * (PHP 4, PHP 5)<br/>
     * Seeks on a file pointer
     * @link http://php.net/manual/en/function.fseek.php
     * @param resource $handle &fs.file.pointer;
     * @param int $offset <p>
     * The offset.
     * </p>
     * <p>
     * To move to a position before the end-of-file, you need to pass
     * a negative value in offset and
     * set whence
     * to SEEK_END.
     * </p>
     * @param int $whence [optional] <p>
     * whence values are:
     * SEEK_SET - Set position equal to offset bytes.
     * SEEK_CUR - Set position to current location plus offset.
     * SEEK_END - Set position to end-of-file plus offset.
     * </p>
     * <p>
     * If whence is not specified, it is assumed to be
     * SEEK_SET.
     * </p>
     *
     * @throws NotAStreamException
     *
     * @return int Upon success, returns 0; otherwise, returns -1. Note that seeking
     * past EOF is not considered an error.
     */
    public function fseek($handle, $offset, $whence = SEEK_SET)
    {
        return $this->runWithErrorHandler('fseek',func_get_args());
    }

    /**
     * (PHP 4, PHP 5)<br/>
     * Returns the current position of the file read/write pointer
     * @link http://php.net/manual/en/function.ftell.php
     * @param resource $handle <p>
     * The file pointer must be valid, and must point to a file successfully
     * opened by fopen or popen.
     * ftell gives undefined results for append-only streams
     * (opened with "a" flag).
     * </p>
     *
     * @throws NotAStreamException
     *
     * @return int the position of the file pointer referenced by
     * handle as an integer; i.e., its offset into the file stream.
     * </p>
     * <p>
     * If an error occurs, returns false.
     */
    public function ftell($handle)
    {
        return $this->runWithErrorHandler('ftell',func_get_args());
    }

    /**
     * (PHP 4, PHP 5)<br/>
     * Truncates a file to a given length
     * @link http://php.net/manual/en/function.ftruncate.php
     * @param resource $handle <p>
     * The file pointer.
     * </p>
     * <p>
     * The handle must be open for writing.
     * </p>
     * @param int $size <p>
     * The size to truncate to.
     * </p>
     * <p>
     * If size is larger than the file it is extended
     * with null bytes.
     * </p>
     * <p>
     * If size is smaller than the extra data
     * will be lost.
     * </p>
     *
     * @throws NotAStreamException
     *
     * @return bool true on success or false on failure.
     */
    public function ftruncate($handle, $size)
    {
        return $this->runWithErrorHandler('ftruncate',func_get_args());
    }

    /**
     * (PHP 4, PHP 5)<br/>
     * Binary-safe file write
     * @link http://php.net/manual/en/function.fwrite.php
     * @param resource $handle &fs.file.pointer;
     * @param string $string <p>
     * The string that is to be written.
     * </p>
     * @param int $length [optional] <p>
     * If the length argument is given, writing will
     * stop after length bytes have been written or
     * the end of string is reached, whichever comes
     * first.
     * </p>
     * <p>
     * Note that if the length argument is given,
     * then the magic_quotes_runtime
     * configuration option will be ignored and no slashes will be
     * stripped from string.
     * </p>
     *
     * @throws NotAStreamException
     *
     * @return int
     */
    public function fwrite($handle, $string, $length = null)
    {
        return $this->runWithErrorHandler('fwrite',func_get_args());
    }

    /**
     * (PHP 4, PHP 5)<br/>
     * Rewind the position of a file pointer
     * @link http://php.net/manual/en/function.rewind.php
     * @param resource $handle <p>
     * The file pointer must be valid, and must point to a file
     * successfully opened by fopen.
     * </p>
     *
     * @throws NotAStreamException
     *
     * @return bool true on success or false on failure.
     */
    public function rewind($handle)
    {
        return $this->runWithErrorHandler('rewind',func_get_args());
    }

    /**
     * (PHP 4, PHP 5)<br/>
     * Attempts to create the directory specified by pathname.
     * @link http://php.net/manual/en/function.mkdir.php
     * @param string $pathname <p>
     * The directory path.
     * </p>
     * @param int $mode [optional] <p>
     * The mode is 0775 by default, which means the widest possible
     * access for owner and group. For more information on modes, read the details
     * on the chmod page.
     * </p>
     * <p>
     * mode is ignored on Windows.
     * </p>
     * <p>
     * Note that you probably want to specify the mode as an octal number,
     * which means it should have a leading zero. The mode is also modified
     * by the current umask, which you can change using
     * umask().
     * </p>
     * @param bool $recursive [optional] <p>
     * Allows the creation of nested directories specified in the pathname. Default to false.
     * </p>
     * @param resource $context [optional] &note.context-support;
     *
     * @throws PermissionException
     * @throws DoesNotExistException
     * @throws NotADirectoryException
     * @throws ExistException
     *
     * @return bool true on success or false on failure.
     */
    public function mkdir($pathname, $mode = 0775, $recursive = false, $context = null)
    {
        return $this->runWithErrorHandler('mkdir',func_get_args());
    }

    /**
     * (PHP 4 &gt;= 4.0.3, PHP 5)<br/>
     * Moves an uploaded file to a new location
     * @link http://php.net/manual/en/function.move-uploaded-file.php
     * @param string $filename <p>
     * The filename of the uploaded file.
     * </p>
     * @param string $destination <p>
     * The destination of the moved file.
     * </p>
     *
     * @throws PermissionException
     * @throws DoesNotExistException
     * @throws NotADirectoryException
     * @throws ExistException
     *
     * @return bool If filename is not a valid upload file,
     * then no action will occur, and
     * move_uploaded_file will return
     * false.
     * </p>
     * <p>
     * If filename is a valid upload file, but
     * cannot be moved for some reason, no action will occur, and
     * move_uploaded_file will return
     * false. Additionally, a warning will be issued.
     */
    public function move_uploaded_file($filename, $destination)
    {
        return $this->runWithErrorHandler('move_uploaded_file',func_get_args());
    }

    /**
     * (PHP 4, PHP 5)<br/>
     * Renames a file or directory
     * @link http://php.net/manual/en/function.rename.php
     * @param string $oldname <p>
     * </p>
     * <p>
     * The old name. The wrapper used in oldname
     * must match the wrapper used in
     * newname.
     * </p>
     * @param string $newname <p>
     * The new name.
     * </p>
     * @param resource $context [optional] &note.context-support;
     *
     * @throws DoesNotExistException
     * @throws PermissionException
     * @throws IsDirectoryException
     *
     * @return bool true on success or false on failure.
     */
    public function rename($oldname, $newname, $context = null)
    {
        return $this->runWithErrorHandler('rename',func_get_args());
    }

    /**
     * (PHP 4, PHP 5)<br/>
     * Removes directory
     * @link http://php.net/manual/en/function.rmdir.php
     * @param string $dirname <p>
     * Path to the directory.
     * </p>
     * @param resource $context [optional] &note.context-support;
     *
     * @throws PermissionException
     * @throws DoesNotExistException
     * @throws NotADirectoryException
     * @throws DirectoryNotEmptyException
     *
     * @return bool true on success or false on failure.
     */
    public function rmdir($dirname, $context = null)
    {
        return $this->runWithErrorHandler('rmdir',func_get_args());
    }

    /**
     * (PHP 5)<br/>
     * List files and directories inside the specified path
     * @link http://php.net/manual/en/function.scandir.php
     * @param string $directory <p>
     * The directory that will be scanned.
     * </p>
     * @param int $sorting_order [optional] <p>
     * By default, the sorted order is alphabetical in ascending order. If
     * the optional sorting_order is set to non-zero,
     * then the sort order is alphabetical in descending order.
     * </p>
     * @param resource $context [optional] <p>
     * For a description of the context parameter,
     * refer to the streams section of
     * the manual.
     * </p>
     *
     * @throws DoesNotExistException
     * @throws PermissionException
     * @throws NotADirectoryException
     *
     * @return array an array of filenames on success, or false on
     * failure.
     */
    public function scandir($directory, $sorting_order = null, $context = null)
    {
        return $this->runWithErrorHandler('scandir',func_get_args());
    }


    /**
     * (PHP 4 &gt;= 4.3.0, PHP 5)<br/>
     * Find pathnames matching a pattern
     * @link http://php.net/manual/en/function.glob.php
     * @param string $pattern <p>
     * The pattern. No tilde expansion or parameter substitution is done.
     * </p>
     * @param int $flags [optional] <p>
     * Valid flags:
     * GLOB_MARK - Adds a slash to each directory returned
     *
     * @throws DoesNotExistException
     * @throws PermissionException
     * @throws NotADirectoryException
     *
     * @return array an array containing the matched files/directories, an empty array
     * if no file matched or false on error.
     * </p>
     * <p>
     * On some systems it is impossible to distinguish between empty match and an
     * error.
     */
    public function glob($pattern, $flags = null)
    {
        return $this->runWithErrorHandler('glob',func_get_args());
    }

    /**
     * (PHP 4, PHP 5)<br/>
     * Gives information about a file
     * @link http://php.net/manual/en/function.stat.php
     * @param string $filename <p>
     * Path to the file.
     * </p>
     *
     * @throws DoesNotExistException
     * @throws PermissionException
     *
     * @return array <table>
     * stat and fstat result
     * format
     * <tr valign="top">
     * <td>Numeric</td>
     * <td>Associative (since PHP 4.0.6)</td>
     * <td>Description</td>
     * </tr>
     * <tr valign="top">
     * <td>0</td>
     * <td>dev</td>
     * <td>device number</td>
     * </tr>
     * <tr valign="top">
     * <td>1</td>
     * <td>ino</td>
     * <td>inode number *</td>
     * </tr>
     * <tr valign="top">
     * <td>2</td>
     * <td>mode</td>
     * <td>inode protection mode</td>
     * </tr>
     * <tr valign="top">
     * <td>3</td>
     * <td>nlink</td>
     * <td>number of links</td>
     * </tr>
     * <tr valign="top">
     * <td>4</td>
     * <td>uid</td>
     * <td>userid of owner *</td>
     * </tr>
     * <tr valign="top">
     * <td>5</td>
     * <td>gid</td>
     * <td>groupid of owner *</td>
     * </tr>
     * <tr valign="top">
     * <td>6</td>
     * <td>rdev</td>
     * <td>device type, if inode device</td>
     * </tr>
     * <tr valign="top">
     * <td>7</td>
     * <td>size</td>
     * <td>size in bytes</td>
     * </tr>
     * <tr valign="top">
     * <td>8</td>
     * <td>atime</td>
     * <td>time of last access (Unix timestamp)</td>
     * </tr>
     * <tr valign="top">
     * <td>9</td>
     * <td>mtime</td>
     * <td>time of last modification (Unix timestamp)</td>
     * </tr>
     * <tr valign="top">
     * <td>10</td>
     * <td>ctime</td>
     * <td>time of last inode change (Unix timestamp)</td>
     * </tr>
     * <tr valign="top">
     * <td>11</td>
     * <td>blksize</td>
     * <td>blocksize of filesystem IO **</td>
     * </tr>
     * <tr valign="top">
     * <td>12</td>
     * <td>blocks</td>
     * <td>number of 512-byte blocks allocated **</td>
     * </tr>
     * </table>
     * * On Windows this will always be 0.
     * </p>
     * <p>
     * ** Only valid on systems supporting the st_blksize type - other
     * systems (e.g. Windows) return -1.
     * </p>
     * <p>
     * In case of error, stat returns false.
     */
    public function stat($filename)
    {
        $handle = $this->fopen($filename,'r');
        $stats = $this->fstat($handle);
        $this->fclose($handle);

        return $stats;
    }


    /**
     * (PHP 4, PHP 5)<br/>
     * Gets information about a file using an open file pointer
     * @link http://php.net/manual/en/function.fstat.php
     * @param resource $handle &fs.file.pointer;
     *
     * @throws NotAStreamException
     *
     * @return array an array with the statistics of the file; the format of the array
     * is described in detail on the stat manual page.
     */
    public function fstat($handle)
    {
        return $this->runWithErrorHandler('fstat',func_get_args());
    }

    /**
     * (PHP 4, PHP 5)<br/>
     * Sets access and modification time of file
     * @link http://php.net/manual/en/function.touch.php
     * @param string $filename <p>
     * The name of the file being touched.
     * </p>
     * @param int $time [optional] <p>
     * The touch time. If time is not supplied,
     * the current system time is used.
     * </p>
     * @param int $atime [optional] <p>
     * If present, the access time of the given filename is set to
     * the value of atime. Otherwise, it is set to
     * time.
     * </p>
     *
     * @throws DoesNotExistException
     * @throws PermissionException
     *
     * @return bool true on success or false on failure.
     */
    public function touch($filename, $time = null, $atime = null)
    {
        return $this->runWithErrorHandler('touch',func_get_args());
    }

    /**
     * (PHP 4, PHP 5)<br/>
     * Deletes a file
     * @link http://php.net/manual/en/function.unlink.php
     * @param string $filename <p>
     * Path to the file.
     * </p>
     * @param resource $context [optional] &note.context-support;
     *
     * @throws DoesNotExistException
     * @throws PermissionException
     * @throws IsDirectoryException
     *
     * @return bool true on success or false on failure.
     */
    public function unlink($filename, $context = null)
    {
        return $this->runWithErrorHandler('unlink',func_get_args());
    }

    /**
     * Wrapper for methods
     *
     * @param $function
     * @param array $arguments
     * @return mixed
     */
    protected function runWithErrorHandler($function,$arguments)
	{
		set_error_handler(function($errno, $errstr){
			throw Exception::factory($errstr,$errno);
		});

		$result = call_user_func_array($function,$arguments);

		restore_error_handler();

		return $result;
	}
} 