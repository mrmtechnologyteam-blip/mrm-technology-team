<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Http\Controller;
use App\Http\Response;
use PDOException;
use Throwable;

final class DashboardController extends Controller
{
    /**
     * نمایش داشبورد مشتری
     */
    public function index(): Response
    {
        if (!$this->isAuthenticated()) {
            return Response::redirect('/login');
        }

        if ($this->isAdmin()) {
            return Response::redirect('/admin/dashboard');
        }

        $userId = $this->currentUserId();

        try {
            $db = db();

            $userQuery = $db->prepare(
                'SELECT
                    id,
                    full_name,
                    email,
                    whatsapp_number,
                    country,
                    created_at
                 FROM users
                 WHERE id = :id
                   AND role = "customer"
                   AND account_status = "active"
                 LIMIT 1'
            );

            $userQuery->execute([
                'id' => $userId,
            ]);

            $user = $userQuery->fetch();

            if (!$user) {
                $_SESSION = [];

                return Response::redirect('/login');
            }

            $projectQuery = $db->prepare(
                'SELECT
                    COUNT(*) AS total,
                    SUM(status = "pending") AS pending,
                    SUM(status = "under_review") AS under_review,
                    SUM(status = "accepted") AS accepted,
                    SUM(status = "completed") AS completed,
                    SUM(status = "rejected") AS rejected
                 FROM projects
                 WHERE user_id = :user_id'
            );

            $projectQuery->execute([
                'user_id' => $userId,
            ]);

            $projectStats = $projectQuery->fetch();

            if (!is_array($projectStats)) {
                $projectStats = [];
            }

            $recentQuery = $db->prepare(
                'SELECT
                    p.id,
                    p.project_code,
                    p.project_title,
                    p.status,
                    p.submitted_at,
                    c.name AS category_name
                 FROM projects p
                 LEFT JOIN project_categories c
                    ON c.id = p.category_id
                 WHERE p.user_id = :user_id
                 ORDER BY p.submitted_at DESC
                 LIMIT 5'
            );

            $recentQuery->execute([
                'user_id' => $userId,
            ]);

            $recentProjects = $recentQuery->fetchAll();

            return $this->view(
                'pages.customer.dashboard',
                [
                    'user' => $user,
                    'projectStats' => $projectStats,
                    'recentProjects' => $recentProjects,
                ]
            );

        } catch (PDOException $exception) {

            error_log(
                '[DashboardController] Database error: ' .
                $exception->getMessage()
            );

            return Response::serverError(
                'دریافت اطلاعات داشبورد با مشکل مواجه شد.'
            );

        } catch (Throwable $exception) {

            error_log(
                '[DashboardController] Unexpected error: ' .
                $exception->getMessage()
            );

            return Response::serverError(
                'خطای غیرمنتظره‌ای رخ داد.'
            );
        }
    }
}
