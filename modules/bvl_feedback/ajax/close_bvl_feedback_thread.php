<?php declare(strict_types=1);

/**
 * File to close a BVL feedback thread via the BVL feedback panel.
 *
 * PHP version 5
 *
 * @category Behavioural
 * @package  Main
 * @author   Evan McIlroy <evanmcilroy@gmail.com>
 * @license  GPLv3 <http://www.gnu.org/licenses/gpl-3.0.en.html>
 * @link     https://www.github.com/aces/Loris-Trunk/
 */

ini_set('default_charset', 'utf-8');
require "bvl_panel_ajax.php";

$user     =& User::singleton();
$username = $user->getUsername();

try {
    $candid = new \LORIS\StudyEntities\Candidate\CandID($_POST['candID'] ?? '');
} catch (\DomainException $e) {
    header("HTTP/1.1 400 Bad Request");
    header("Content-Type: application/json");
    print json_encode(['error' => 'invalid candID']);
    exit(0);
}

if (!isset($_POST['feedbackID'])) {
    header("HTTP/1.1 400 Bad Request");
    header("Content-Type: application/json");
    print json_encode(['error' => 'Missing FeedbackID']);
    exit(0);
}

$feedbackid = $_POST['feedbackID'];

try {
    $feedbackThread =& NDB_BVL_Feedback::Singleton($username, $candid);

    // FIXME This allows to close any thread as long as the feedbackid
    //       exists. It does not have to be related to this feedbackthread.
    $closethreadcount = $feedbackThread->closeThread((int) $feedbackid);
} catch (\Exception $e) {
    error_log($e->getMessage());
    header("HTTP/1.1 404 Not Found");
    header("Content-Type: application/json");
    print json_encode(
        ['error' => 'The requested feedback thread can`t be found']
    );
    exit(0);
}

if ($closethreadcount === 0) {
    header("HTTP/1.1 500 Internal Server Error");
    header("Content-Type: application/json");
    print json_encode(
        ['error' => 'No feedback thread updated']
    );
    exit(0);
}

header("content-type:application/json");
echo json_encode(['success' => true]);
exit(0);


