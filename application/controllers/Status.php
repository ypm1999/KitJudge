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
        session_start();
        session_write_close();
        $this->load->library('KitInfo');
        $this->load->model('KitStatus');
        $this->load->model('KitProblem');
        $this->load->database(KitInfo::$kitInfo['kitDatabase']);
        $result = $this->KitStatus->kitGetStatusExistsById($_POST['id']);
        if (!isset($_SESSION['kitUser']) || !isset($_POST['session'])
            || !isset($_POST['id']) || !is_numeric($_POST['id'])
            || empty($result->result_array())
        ) {
            exit(json_encode(array(
                'verdict' => false,
                'message' => 'Invalid request.'
            )));
        }
        $result = $result->row();
        if ($result->kitStatusVerdict == 0) {
            $this->KitProblem->kitDecreaseAccepted($result->kitStatusProbId);
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
