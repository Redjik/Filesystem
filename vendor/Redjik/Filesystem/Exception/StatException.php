<?php
/**
 * @author Ivan Matveev <Redjiks@gmail.com>.
 */

namespace Redjik\Filesystem\Exception;


/**
 * Class StatException
 *
 * Usually thrown when file or directory doesn't exist
 * Or when there is no enough permissions
 * Only for stat functions:
 * fileatime
 * filectime
 * filegroup
 * fileinode
 * filemtime
 * fileowner
 * fileperms
 * filesize
 * filetype
 *
 * @package Redjik\Filesystem\Exception
 */
class StatException extends Exception
{

} 