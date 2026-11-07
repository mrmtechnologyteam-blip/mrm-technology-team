<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Http\Controller;
use App\Http\Request;
use App\Http\Response;
use PDO;

final class ProjectDetailsController extends Controller
{
    public function __construct(
        Request $request,
        array $parameters = []
    ) {
        parent::__construct($request, $parameters);
    }

    /**
     * Display project details and tracking history.
     */
    public function show(): Response
    {
        // Authentication
        $authResponse = $this->requireAuthentication();

        if ($authResponse !== null) {
            return $authResponse;
        }

        // Validate project ID
        $projectId = filter_var(
            $this->parameter('id'),
            FILTER_VALIDATE_INT
        );

        if (!$projectId || $projectId <= 0) {
            return Response::notFound();
        }

        $userId = $this->currentUserId();
        $isAdmin = $this->isAdmin();

        try {
            $database = db();

            /*
             * Retrieve project information.
             * Administrators can view all projects.
             * Customers can only view their own projects.
             */

            $sql = "
                SELECT
                    p.*,
                    u.full_name AS customer_name,
                    u.email AS customer_email,
                    u.whatsapp_number AS customer_whatsapp,
                    c.name AS category_name

                FROM projects p

                INNER JOIN users u
                    ON u.id = p.user_id

                INNER JOIN project_categories c
                    ON c.id = p.category_id

                WHERE p.id = :project_id
            ";

            $parameters = [
                'project_id' => $projectId
            ];

            if (!$isAdmin) {
                $sql .= " AND p.user_id = :user_id";

                $parameters['user_id'] = $userId;
            }

            $sql .= " LIMIT 1";

            $projectStatement = $database->prepare($sql);

            $projectStatement->execute($parameters);

            $project = $projectStatement->fetch(PDO::FETCH_ASSOC);

            if (!$project) {
                return Response::notFound();
            }

            /*
             * Retrieve questionnaire answers.
             */

            $answerStatement = $database->prepare("
                SELECT
                    pa.id AS answer_id,
                    pa.answer_value,
                    pq.id AS question_id,
                    pq.section_number,
                    pq.question_number,
                    pq.question_text,
                    pq.question_type

                FROM project_answers pa

                INNER JOIN project_questions pq
                    ON pq.id = pa.question_id

                WHERE pa.project_id = :project_id

                ORDER BY
                    pq.section_number ASC,
                    pq.question_number ASC
            ");

            $answerStatement->execute([
                'project_id' => $projectId
            ]);

            $answers = $answerStatement->fetchAll(
                PDO::FETCH_ASSOC
            );

            /*
             * Group answers by questionnaire section.
             */

            $sections = [];

            foreach ($answers as $answer) {
                $sectionNumber = (int) $answer['section_number'];

                if (!isset($sections[$sectionNumber])) {
                    $sections[$sectionNumber] = [];
                }

                $value = $answer['answer_value'];

                // Decode JSON answers when possible.
                if (is_string($value)) {
                    $decoded = json_decode($value, true);

                    if (json_last_error() === JSON_ERROR_NONE) {
                        $value = $decoded;
                    }
                }

                $answer['display_value'] = $value;

                $sections[$sectionNumber][] = $answer;
            }

            /*
             * Retrieve project status history.
             */

            $historyStatement = $database->prepare("
                SELECT
                    h.id,
                    h.old_status,
                    h.new_status,
                    h.note,
                    h.changed_at,
                    u.full_name AS changed_by_name

                FROM project_status_history h

                LEFT JOIN users u
                    ON u.id = h.changed_by

                WHERE h.project_id = :project_id

                ORDER BY h.changed_at DESC, h.id DESC
            ");

            $historyStatement->execute([
                'project_id' => $projectId
            ]);

            $history = $historyStatement->fetchAll(
                PDO::FETCH_ASSOC
            );

            /*
             * Display project details.
             */

            return $this->view(
                'pages.projects.show',
                [
                    'project' => $project,
                    'sections' => $sections,
                    'history' => $history,
                    'isAdmin' => $isAdmin
                ]
            );

        } catch (\Throwable $exception) {
            error_log(
                'Project details error: ' .
                $exception->getMessage()
            );

            return Response::serverError(
                'در دریافت اطلاعات پروژه مشکلی رخ داده است.'
            );
        }
    }
}
