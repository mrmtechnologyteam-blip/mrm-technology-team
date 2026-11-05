<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Http\Controller;
use App\Http\Response;
use PDOException;
use Throwable;

final class AdminDashboardController extends Controller
{
    /**
     * نمایش داشبورد مدیریت
     */
    public function index(): Response
    {
        if (!$this->isAuthenticated()) {
            return Response::redirect('/login');
        }

        if (!$this->isAdmin()) {
            return Response::forbidden();
        }

        try {
            $db = db();

            // بررسی اعتبار حساب مدیر
            $adminQuery = $db->prepare(
                'SELECT id, full_name, email, created_at
                 FROM users
                 WHERE id = :id
                   AND role = "admin"
                   AND account_status = "active"
                 LIMIT 1'
            );

            $adminQuery->execute([
                'id' => $this->currentUserId(),
            ]);

            $admin = $adminQuery->fetch();

            if (!$admin) {
                $_SESSION = [];

                return Response::redirect('/login');
            }

            // آمار کاربران
            $userStats = $db->query(
                'SELECT
                    COUNT(*) AS total,
                    SUM(role = "customer") AS customers,
                    SUM(role = "admin") AS admins,
                    SUM(account_status = "active") AS active,
                    SUM(account_status = "pending") AS pending,
                    SUM(account_status = "suspended") AS suspended
                 FROM users'
            )->fetch();

            // آمار پروژه‌ها
            $projectStats = $db->query(
                'SELECT
                    COUNT(*) AS total,
                    SUM(status = "pending") AS pending,
                    SUM(status = "under_review") AS under_review,
                    SUM(status = "accepted") AS accepted,
                    SUM(status = "completed") AS completed,
                    SUM(status = "rejected") AS rejected
                 FROM projects'
            )->fetch();

            // تعداد دسته‌بندی‌ها
            $categoryStats = $db->query(
                'SELECT
                    COUNT(*) AS total,
                    SUM(is_active = 1) AS active
                 FROM project_categories'
            )->fetch();

            // پنج پروژه اخیر
            $recentProjectsQuery = $db->query(
                'SELECT
                    p.id,
                    p.project_code,
                    p.project_title,
                    p.status,
                    p.submitted_at,
                    u.full_name AS customer_name,
                    u.email AS customer_email,
                    c.name AS category_name
                 FROM projects p
                 INNER JOIN users u ON u.id = p.user_id
                 LEFT JOIN project_categories c
                    ON c.id = p.category_id
                 ORDER BY p.submitted_at DESC
                 LIMIT 5'
            );

            $recentProjects = $recentProjectsQuery->fetchAll();

            // پنج کاربر جدید
            $recentUsersQuery = $db->query(
                'SELECT
                    id,
                    full_name,
                    email,
                    account_status,
                    created_at
                 FROM users
                 WHERE role = "customer"
                 ORDER BY created_at DESC
                 LIMIT 5'
            );

            $recentUsers = $recentUsersQuery->fetchAll();

            return $this->view(
                'pages.admin.dashboard',
                [
                    'admin' => $admin,
                    'userStats' => $userStats ?: [],
                    'projectStats' => $projectStats ?: [],
                    'categoryStats' => $categoryStats ?: [],
                    'recentProjects' => $recentProjects,
                    'recentUsers' => $recentUsers,
                ]
            );

        } catch (PDOException $exception) {

            error_log(
                '[AdminDashboardController] Database error: ' .
                $exception->getMessage()
            );

            return Response::serverError(
                'دریافت اطلاعات داشبورد مدیریت با مشکل مواجه شد.'
            );

        } catch (Throwable $exception) {

            error_log(
                '[AdminDashboardController] Unexpected error: ' .
                $exception->getMessage()
            );

            return Response::serverError(
                'خطای غیرمنتظره‌ای در داشبورد مدیریت رخ داد.'
            );
        }
    }
}
