<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Status extends CI_Controller
{
    public function filter()
    {
        session_start();
        $_SESSION['status-filter'] = array();
        if (isset($_POST['user'])) {
            $_SESSION['status-filter'] = array_merge(
                $_SESSION['status-filter'],
                array('user' => $_POST['user'])
            );
        }
        if (isset($_POST['prob'])) {
            $_SESSION['status-filter'] = array_merge(
                $_SESSION['status-filter'],
                array('prob' => $_POST['prob'])
            );
        }
        if (isset($_POST['verdict'])) {
            $_SESSION['status-filter'] = array_merge(
                $_SESSION['status-filter'],
                array('verdict' => $_POST['verdict'])
            );
        }
        if (isset($_POST['lang'])) {
            $_SESSION['status-filter'] = array_merge(
                $_SESSION['status-filter'],
                array('lang' => $_POST['lang'])
            );
        }
        if (empty($_SESSION['status-filter'])) {
            unset($_SESSION['status-filter']);
        }
        session_write_close();
    }

    public function rejudge()
    {
        if (!isset($_POST) || !isset($_POST['session'])) {
            exit(json_encode(array(
                'verdict' => false,
                'message' => 'Invalid request.'
            )));
        }
        $this->load->library('KitInfo');
        $this->load->library('KitFile');
        $this->load->model('KitProblem');
        $this->load->model('KitStatus');
        session_start();
        session_write_close();
        if (!isset($_SESSION['kitUser']) || $_POST['session'] != $_COOKIE['session']) {
            exit(json_encode(array(
                'verdict' => false,
                'message' => 'session expired.',
            )));
        }
        if (!isset($_SESSION['kitUser']) || $_SESSION['kitUser']['priority'] < 2
            || !isset($_POST['session']) || !isset($_POST['id']) || !is_numeric($_POST['id'])
            || empty($result->result_array())) {
            exit(json_encode(array(
                'verdict' => false,
                'message' => 'Invalid request.'
            )));
        }
        $this->load->database(KitInfo::$kitInfo['kitDatabase']);
        $result = $this->KitStatus->kitGetStatusExistsById($_POST['id']);
        $result = $result->row();
        if ($result->kitStatusVerdict == 0) {
            $this->KitProblem->kitDecreaseAccepted($result->kitStatusProbId);
        }

        $problem = $this->KitProblem->kitGetProblemById($result->kitStatusProbId, 5);
        $probtype = $problem->row()->kitProbType;
        $problem = json_decode(file_get_contents('files/probfile/' . $_POST['prob'] . '/problem.json'));
        foreach ($problem->files as $caption => $conf) {
            if (!isset($_POST[$caption]) || !isset($_POST[$caption . 'lang'])) {
                exit(json_encode(array(
                    'verdict' => false,
                    'message' => 'Invalid request.'
                )));
            }
            if ($submit_lang == null) {
                $submit_lang = $_POST[$caption . 'lang'];
            } else {
                $submit_lang = 'Mixed';
            }
            $submit_length += strlen($_POST[$caption]);
        }
        $commit_data = array_merge($commit_data, array(
            'probid' => $result->kitStatusProbId,
            'type' => $probtype,
            'runid' => $result->kitStatusId,
            'user' => $result->kitStatusUser,
            'version' => KitFile::kitGetVersion()
        ));
        if (!is_dir('files/userfile/' . $_SESSION['kitUser']['name'] . '/code/' . $commit_data['runid'])) {
            mkdir('files/userfile/' . $_SESSION['kitUser']['name'] . '/code/' . $commit_data['runid'], 0777, true);
            chmod('files/userfile/' . $_SESSION['kitUser']['name'] . '/code/' . $commit_data['runid'], 0777);
        }
        if ($commit_data['runid'] == -1) {
            exit(json_encode(array(
                'verdict' => false,
                'message' => 'Cannot insert such status.'
            )));
        }
        foreach ($problem->files as $caption => $file) {
            if (!isset($_POST[$caption])) {
                exit(json_encode(array(
                    'verdict' => false,
                    'message' => 'Invalid request.'
                )));
            }
            file_get_contents('files/userfile/' . $_SESSION['kitUser']['name'] . '/code/' . $commit_data['runid'] . '/' . $caption, $_POST[$caption]);
            file_get_contents('files/userfile/' . $_SESSION['kitUser']['name'] . '/code/' . $commit_data['runid'] . '/' . $caption . 'lang', $_POST[$caption . 'lang']);
            $commit_data = array_merge($commit_data, array($caption => file_get_contents('files/userfile/' . $_SESSION['kitUser']['name'] . '/code/' . $commit_data['runid'] . '/' . $caption, $_POST[$caption])));
            $commit_data = array_merge($commit_data, array($caption . 'lang' => file_get_contents('files/userfile/' . $_SESSION['kitUser']['name'] . '/code/' . $commit_data['runid'] . '/' . $caption . 'lang', $_POST[$caption . 'lang'])));
        }
        $this->load->library('KitMQHandler');
        if (!KitMQHandler::publish(KitInfo::$kitInfo['kitMQ'], json_encode($commit_data))) {
            echo json_encode(array(
                'verdict' => false,
                'message' => 'Cannot publish judge task.'
            ));
        } else {
            echo json_encode(array(
                'verdict' => true,
                'runid' => $commit_data['runid']
            ));
        }
    }

    public function details($statusId = null)
    {
        if (!is_numeric($statusId)) {
            show_404();
        }
        session_start();
        session_write_close();
        $this->load->library('KitInfo');
        $this->load->model('KitStatus');
        $this->load->model('KitProblem');
        $this->load->model('KitContest');
        $this->load->database(KitInfo::$kitInfo['kitDatabase']);
        $result = $this->KitStatus->kitGetStatusById($statusId);
        if (empty($result->result_array())) {
            show_404();
        }
        $result = $result->row();
        if (!isset($_SESSION['kitUser']) || ($_SESSION['kitUser']['priority'] < 2
                && $_SESSION['kitUser']['name'] != $result->kitStatusUser)
            || ($result->kitStatusContestId != null
                && !$this->isContestStart($this->KitContest->kitGetContestById($result->kitStatusContestId)))
        ) {
            show_404();
        }
        $info = array_merge(KitInfo::$kitInfo, array(
            'kitId' => (int)($statusId),
            'kitVerdict' => $result->kitStatusVerdict,
            'kitUser' => $result->kitStatusUser,
            'kitView' => 'status-details',
            'kitPageTitle' => 'Detailed Status',
            'kitConf' => json_decode(file_get_contents("files/probfile/$result->kitStatusProbId/problem.json")),
            'kitShowReport' => (($_SESSION['kitUser']['priority'] >= 2) || (
                $result->kitStatusContestId != null
                    ? $this->isContestEnded($this->KitContest->kitGetContestById($result->kitStatusContestId))
                    : false
                )
            )
        ));
        $this->load->view('kit-common-header', $info);
        $this->load->view('kit-navbar');
        $this->load->view('kit-modal', $info);
        $this->load->view('status/details/kit-status-details-preload', $info);
        $this->load->view('status/details/kit-status-details', $info);
        $this->load->view('kit-common-bottom');
        $this->load->view('status/details/kit-status-details-footage');
        $this->load->view('kit-common-footage');
    }

    private function isContestStart($contest)
    {
        return time() >= strtotime($contest->row()->kitContestStart);
    }

    private function isContestEnded($contest)
    {
        return time() >= strtotime($contest->row()->kitContestEnd);
    }

    public function index()
    {
        $this->load->library('KitInfo');
        $this->load->model('KitStatus');
        $this->load->model('KitContest');
        session_start();
        session_write_close();
        $this->load->database(KitInfo::$kitInfo['kitDatabase']);
        $info = array_merge(KitInfo::$kitInfo,
            array(
                'kitView' => 'status',
                'kitCurPage' => isset($_GET['page']) ? (int)($_GET['page']) : 1,
                'kitPageTitle' => 'Runtime Status',
                'kitSessionId' => session_id(),
                'kitStatus' => $this->KitStatus->kitGetStatusByFiliter(
                    time(),
                    isset($_SESSION['kitUser']) ? $_SESSION['kitUser'] : null,
                    isset($_SESSION['status-filter']['user']) ? $_SESSION['status-filter']['user'] : null,
                    isset($_SESSION['status-filter']['prob']) ? $_SESSION['status-filter']['prob'] : null,
                    isset($_SESSION['status-filter']['lang']) ? $_SESSION['status-filter']['lang'] : null,
                    isset($_SESSION['status-filter']['verdict']) ? $_SESSION['status-filter']['verdict'] : null,
                    isset($_GET['page']) ? (int)($_GET['page']) : 1,
                    KitInfo::$kitInfo['kitStatusPERPage']
                )->result(),
                'kitStatusTotalPages' => max((int)floor(
                    (
                        $this->KitStatus->kitCountStatus(
                            time(),
                            isset($_SESSION['kitUser']) ? $_SESSION['kitUser'] : null,
                            isset($_SESSION['status-filter']['user']) ? $_SESSION['status-filter']['user'] : null,
                            isset($_SESSION['status-filter']['prob']) ? $_SESSION['status-filter']['prob'] : null,
                            isset($_SESSION['status-filter']['lang']) ? $_SESSION['status-filter']['lang'] : null,
                            isset($_SESSION['status-filter']['verdict']) ? $_SESSION['status-filter']['verdict'] : null
                        )
                        + KitInfo::$kitInfo['kitStatusPERPage'] - 1
                    ) / KitInfo::$kitInfo['kitStatusPERPage']),
                    1
                )
            )
        );
        foreach ($info['kitStatus'] as $status) {
            $status->valid = ((!isset($_SESSION['kitUser']) || ($_SESSION['kitUser']['priority'] < 2
                    && $_SESSION['kitUser']['name'] != $status->kitStatusUser)
                || ($status->kitStatusContestId != null
                    && !$this->isContestStart($this->KitContest->kitGetContestById($status->kitStatusContestId)))
                )) ? false : true;
        }
        setcookie('session', $info['kitSessionId']);
        $this->load->view('kit-common-header', $info);
        $this->load->view('kit-navbar');
        $this->load->view('kit-modal', $info);
        $this->load->view('status/kit-status-preload', $info);
        $this->load->view('status/kit-status', $info);
        $this->load->view('kit-common-bottom');
        $this->load->view('status/kit-status-footage');
        $this->load->view('kit-common-footage');
    }
}
