<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class User extends CI_Controller
{
    public function register()
    {
        if (!isset($_POST) || !isset($_POST['username']) || !isset($_POST['password']) || !isset($_POST['email']) || !$this->input->is_ajax_request()) {
            echo json_encode(array(
                'verdict' => false,
                'message' => 'Invalid request.'
            ));
        } else {
            $username = $_POST['username'];
            $password = $_POST['password'];
            $email = $_POST['email'];
            $session = $_POST['session'];
            if ($session != $_COOKIE['session']) {
                echo json_encode(array(
                    'verdict' => false,
                    'message' => 'session expired.',
                ));
            } else {
                $this->load->model('KitUser');
                $this->load->library('KitInfo');
                $this->load->database(KitInfo::$kitInfo['kitDatabase']);
                $verdict = $this->KitUser->kitInsertUser($username, $password, $email);
                if ($verdict['verdict']) {
                    echo json_encode(array(
                        'verdict' => true
                    ));
                } else {
                    echo json_encode(array(
                        'verdict' => false,
                        'message' => $verdict['message']
                    ));
                }
            }
        }
    }

    public function login()
    {
        if (!isset($_POST) || !isset($_POST['username']) || !isset($_POST['password']) || !isset($_POST['session']) || !$this->input->is_ajax_request()) {
            echo json_encode(array(
                'verdict' => false,
                'message' => 'session expired.'
            ));
        } else {
            $username = $_POST['username'];
            $password = $_POST['password'];
            $session = $_POST['session'];
            if ($session != $_COOKIE['session']) {
                echo json_encode(array(
                    'verdict' => false,
                    'message' => 'session expired.',
                ));
            } else {
                $this->load->model('KitUser');
                $this->load->library('KitInfo');
                $this->load->database(KitInfo::$kitInfo['kitDatabase']);
                $verdict = $this->KitUser->kitVerdictUser($username, $password);
                if ($verdict['verdict']) {
                    session_start();
                    $_SESSION['kitUser'] = array(
                        'name' => $verdict['username'],
                        'priority' => $verdict['priority']
                    );
                    session_write_close();
                    echo json_encode(array(
                        'verdict' => true
                    ));
                } else {
                    echo json_encode(array(
                        'verdict' => false,
                        'message' => $verdict['message']
                    ));
                }
            }
        }
    }

    public function logout()
    {
        session_start();
        unset($_SESSION['kitUser']);
        session_write_close();
    }
}
