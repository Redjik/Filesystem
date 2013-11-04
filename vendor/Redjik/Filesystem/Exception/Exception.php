<?php
/**
 * @author Ivan Matveev <Redjiks@gmail.com>.
 */

namespace Redjik\Filesystem\Exception;


abstract class Exception extends \Exception
{
    protected static $errors = [
        'Permission denied' => 'PermissionException',
        'File exists' => 'ExistException',
        'Operation not permitted' => 'PermissionException',
        'No such file or directory' => 'DoesNotExistException',
        'Is a directory' => 'IsDirectoryException',
        'cannot be a directory' => 'IsDirectoryException',
        'Not a directory' => 'NotADirectoryException',
        'Directory not empty' => 'DirectoryNotEmptyException',
        'stat failed for' => 'StatException',

        'is not a valid stream resource'=>'NotAStreamException',
    ];

    /**
     * @param string $errstr
     * @param number $errno
     * @return Exception
     */
    public static function factory($errstr,$errno)
    {
        if ($errno !== E_WARNING){
            return new NotAWarningException($errstr,$errno);
        }

        foreach (static::$errors as $errorString => $exception)
        {
            if (strstr($errstr,$errorString)!==false){
                $exceptionClass = __NAMESPACE__.'\\'.$exception;
                return new $exceptionClass($errstr,$errno);
            }
        }

        return new UnknownErrorException($errstr,$errno);

    }
} 