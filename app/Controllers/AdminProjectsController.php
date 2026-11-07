<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Http\Controller;
use App\Http\Request;
use App\Http\Response;
use PDO;
use Throwable;

final class AdminProjectsController extends Controller
{
    private const STATUSES = [
        'pending',
        'under_review',
        'accepted',
        'rejected',
        'completed',
    ];

    public function __construct(
        Request $request,
        array $parameters = []
    ) {
        parent::__construct($request, $parameters);
    }

    /**
     * Display and filter all projects.
     */
    public function index(): Response
    {
        $authResponse = $this->requireAuthentication();

        if ($authResponse !== null) {
            return $authResponse;
        }

        $adminResponse = $this->requireAdmin();

        if ($adminResponse !== null) {
            return $adminResponse;
        }

        try {
            $database = db();

            $search = trim((string) $this->query('search', ''));

            $status = trim((string) $this->query('status', ''));

            $categoryId = filter_var(
                $this->query('category', ''),
                FILTER_VALIDATE_INT
            );

            $page = filter_var(
                $this->query('page', 1),
                FILTER_VALIDATE_INT
            );

            $page = $page && $page > 0 ? $page : 1;

            $perPage = 15;

            if (!in_array($status, self::STATUSES, true)) {
                $status = '';
            }

            if (!$categoryId || $categoryId <= 0) {
                $categoryId = null;
            }

            if (mb_strlen($search, 'UTF-8') > 200) {
                $search = mb_substr($search, 0, 200, 'UTF-8');
            }

            /*
             * Build filtering conditions.
             */

            $conditions = [];
            $parameters = [];

            if ($search !== '') {
                $conditions[] = "(
                    p.project_code LIKE :search_code
                    OR p.project_title LIKE :search_title
                    OR u.full_name LIKE :search_name
                    OR u.email LIKE :search_email
                )";

                $searchValue = '%' . $search . '%';

                $parameters['search_code'] = $searchValue;
                $parameters['search_title'] = $searchValue;
                $parameters['search_name'] = $searchValue;
                $parameters['search_email'] = $searchValue;
            }

            if ($status !== '') {
                $conditions[] = 'p.status = :status';
                $parameters['status'] = $status;
            }

            if ($categoryId !== null) {
                $conditions[] = 'p.category_id = :category_id';
                $parameters['category_id'] = $categoryId;
            }

            $where = '';

            if (!empty($conditions)) {
                $where = ' WHERE ' . implode(' AND ', $conditions);
            }

            /*
             * Count matching projects.
             */

            $countSql = "
                SELECT COUNT(*)
                FROM projects p
                INNER JOIN users u ON u.id = p.user_id
                LEFT JOIN project_categories c
                    ON c.id = p.category_id
                {$where}
            ";

            $countStatement = $database->prepare($countSql);
            $countStatement->execute($parameters);

            $totalProjects = (int) $countStatement->fetchColumn();

            $totalPages = max(
                1,
                (int) ceil($totalProjects / $perPage)
            );

            $page = min($page, $totalPages);

            $offset = ($page - 1) * $perPage;

            /*
             * Retrieve filtered projects.
             */

            $projectsSql = "
                SELECT
                    p.id,
                    p.project_code,
                    p.project_title,
                    p.project_description,
                    p.status,
                    p.budget,
                    p.budget_currency,
                    p.expected_delivery_date,
                    p.submitted_at,
                    p.updated_at,

                    u.full_name AS customer_name,
                    u.email AS customer_email,

                    c.name AS category_name

                FROM projects p

                INNER JOIN users u
                    ON u.id = p.user_id

                LEFT JOIN project_categories c
                    ON c.id = p.category_id

                {$where}

                ORDER BY p.submitted_at DESC, p.id DESC

                LIMIT :limit OFFSET :offset
            ";

            $projectsStatement = $database->prepare($projectsSql);

            foreach ($parameters as $key => $value) {
                $projectsStatement->bindValue(
                    ':' . $key,
                    $value
                );
            }

            $projectsStatement->bindValue(
                ':limit',
                $perPage,
                PDO::PARAM_INT
            );

            $projectsStatement->bindValue(
                ':offset',
                $offset,
                PDO::PARAM_INT
            );

            $projectsStatement->execute();

            $projects = $projectsStatement->fetchAll(
                PDO::FETCH_ASSOC
            );

            /*
             * Retrieve project categories.
             */

            $categoriesStatement = $database->query("
                SELECT id, name
                FROM project_categories
                ORDER BY name ASC
            ");

            $categories = $categoriesStatement->fetchAll(
                PDO::FETCH_ASSOC
            );

            /*
             * Retrieve status totals.
             */

            $statusStatistics = $database->query("
                SELECT
                    COUNT(*) AS total,
                    SUM(status = 'pending') AS pending,
                    SUM(status = 'under_review') AS under_review,
                    SUM(status = 'accepted') AS accepted,
                    SUM(status = 'rejected') AS rejected,
                    SUM(status = 'completed') AS completed
                FROM projects
            ")->fetch(PDO::FETCH_ASSOC);

            return $this->view(
                'pages.admin.projects.index',
                [
                    'projects' => $projects,
                    'categories' => $categories,

                    'filters' => [
                        'search' => $search,
                        'status' => $status,
                        'category' => $categoryId,
                    ],

                    'pagination' => [
                        'current_page' => $page,
                        'per_page' => $perPage,
                        'total_projects' => $totalProjects,
                        'total_pages' => $totalPages,
                    ],

                    'statusStatistics' => $statusStatistics ?: [],
                ]
            );

        } catch (Throwable $exception) {
            error_log(
                '[AdminProjectsController] Error: ' .
                $exception->getMessage()
            );

            return Response::serverError(
                'دریافت فهرست پروژه‌ها با مشکل مواجه شد.'
            );
        }
    }
}
