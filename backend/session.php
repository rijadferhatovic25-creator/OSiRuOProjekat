<?php
session_save_path('/tmp');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}