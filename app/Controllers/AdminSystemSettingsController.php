<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Http\Controller;
use App\Http\Response;
use App\Models\SystemSetting;
use InvalidArgumentException;
use PDOException;
use Throwable;

final class AdminSystemSettingsController extends Controller
{
    /**
     * نمایش تنظیمات سیستم در پنل مدیریت
     */
    public function index(): Response
    {
        if (!$this->isAuthenticated()) {
            return $this->redirect('/login');
        }

        if (!$this->isAdmin()) {
            return Response::forbidden();
        }

        try {
            $settingsModel = new SystemSetting(db());

            $settings = $settingsModel->getAll();

            $groupedSettings = [];

            foreach ($settings as $setting) {
                $group = $setting['setting_group'];

                if (!isset($groupedSettings[$group])) {
                    $groupedSettings[$group] = [];
                }

                $groupedSettings[$group][] = $setting;
            }

            return $this->view(
                'pages.admin.settings.index',
                [
                    'settings' => $settings,
                    'groupedSettings' => $groupedSettings,
                    'totalSettings' => count($settings),
                ]
            );

        } catch (PDOException $exception) {
            error_log(
                '[AdminSystemSettingsController] Database error: '
                . $exception->getMessage()
            );

            return Response::serverError(
                'دریافت تنظیمات سیستم با مشکل مواجه شد.'
            );

        } catch (Throwable $exception) {
            error_log(
                '[AdminSystemSettingsController] Unexpected error: '
                . $exception->getMessage()
            );

            return Response::serverError(
                'خطای غیرمنتظره‌ای در بخش تنظیمات رخ داد.'
            );
        }
    }

    /**
     * ذخیره تنظیمات سیستم
     */
    public function update(): Response
    {
        if (!$this->isAuthenticated()) {
            return $this->redirect('/login');
        }

        if (!$this->isAdmin()) {
            return Response::forbidden();
        }

        $methodResponse = $this->requirePost();

        if ($methodResponse !== null) {
            return $methodResponse;
        }

        $csrfResponse = $this->validateCsrf();

        if ($csrfResponse !== null) {
            return $csrfResponse;
        }

        try {
            $submittedSettings = $this->post('settings', []);

            if (!is_array($submittedSettings)) {
                return $this->error(
                    'اطلاعات تنظیمات نامعتبر است.',
                    '/admin/settings'
                );
            }

            $settingsModel = new SystemSetting(db());

            $existingSettings = $settingsModel->getAll();

            $existingByKey = [];

            foreach ($existingSettings as $setting) {
                $existingByKey[$setting['setting_key']] = $setting;
            }

            $updatedCount = 0;

            foreach ($submittedSettings as $key => $value) {
                if (!is_string($key) || !isset($existingByKey[$key])) {
                    continue;
                }

                $setting = $existingByKey[$key];

                $type = $setting['setting_type'];

                if ($type === 'boolean') {
                    $value = in_array(
                        strtolower((string) $value),
                        ['1', 'true', 'yes', 'on'],
                        true
                    );
                }

                $settingsModel->set(
                    $key,
                    $value,
                    $type,
                    $setting['setting_group'],
                    $setting['setting_label'],
                    $setting['description'],
                    (bool) $setting['is_public'],
                    $this->currentUserId()
                );

                $updatedCount++;
            }

            // مدیریت checkboxهایی که ارسال نشده‌اند
            foreach ($existingSettings as $setting) {
                if (
                    $setting['setting_type'] === 'boolean'
                    && !array_key_exists(
                        $setting['setting_key'],
                        $submittedSettings
                    )
                ) {
                    $settingsModel->set(
                        $setting['setting_key'],
                        false,
                        'boolean',
                        $setting['setting_group'],
                        $setting['setting_label'],
                        $setting['description'],
                        (bool) $setting['is_public'],
                        $this->currentUserId()
                    );
                }
            }

            return $this->success(
                'تنظیمات سیستم با موفقیت ذخیره شد.',
                '/admin/settings'
            );

        } catch (InvalidArgumentException $exception) {
            return $this->error(
                'اطلاعات یکی از تنظیمات معتبر نیست.',
                '/admin/settings'
            );

        } catch (PDOException $exception) {
            error_log(
                '[AdminSystemSettingsController] Update database error: '
                . $exception->getMessage()
            );

            return Response::serverError(
                'ذخیره تنظیمات سیستم با مشکل مواجه شد.'
            );

        } catch (Throwable $exception) {
            error_log(
                '[AdminSystemSettingsController] Update error: '
                . $exception->getMessage()
            );

            return Response::serverError(
                'خطای غیرمنتظره‌ای هنگام ذخیره تنظیمات رخ داد.'
            );
        }
    }
}
