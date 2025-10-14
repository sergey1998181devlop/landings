<?php

require_once('View.php');

class FaqView extends View {
    use \api\traits\JWTAuthTrait;

    public function fetch() {
        $action = $this->request->get('action');

        if ($action === 'section') {
            return $this->renderFaqSection(false);
        } elseif ($action === 'user_section') {
            return $this->renderFaqSection(true);
        }

        $blockKey = $this->request->get('block');

        // Если параметр block не передан, считаем что используется /faq
        // Значит нужно показать блок "application_process"
        if (!$blockKey) {
            $blockKey = 'application_process';
        }

        // Обработка /user/faq
        if ($blockKey == 'user') {
            $this->jwtAuthValidate();

            if (empty($this->user)) {
                header('Location: ' . $this->config->root_url . '/user/login');
                exit();
            }

            $userBalance = $this->users->get_user_balance($this->user->id);
            $userLastLoan = $this->orders->get_last_order($this->user->id);

            $blockTypes = [];

            $zaimDate = $userBalance->zaim_date ?? '';
            $paymentDate = $userBalance->payment_date ?? '';
            $haveClosedLoans = !empty($userLastLoan->have_close_credits);
            $isComplete = !empty($userLastLoan->complete);

            $defaultZaimDate = '0001-01-01T00:00:00';
            $defaultPaymentDate = '0001-01-01 00:00:00';
            $todayDate = date('Y-m-d H:i:s');

            // 1. Закрытые займы
            if ($zaimDate === $defaultZaimDate && $isComplete && $haveClosedLoans) {
                $blockTypes[] = Faq::BLOCK_TYPES['closed_loans'];
            } // 2. Активный займ
            elseif ($zaimDate !== $defaultZaimDate && $paymentDate !== $defaultPaymentDate && $paymentDate > $todayDate) {
                $blockTypes[] = Faq::BLOCK_TYPES['active_loan'];
            } // 3. Просроченный займ
            elseif ($zaimDate !== $defaultZaimDate && $paymentDate !== $defaultPaymentDate && $paymentDate < $todayDate) {
                $blockTypes[] = Faq::BLOCK_TYPES['overdue_debt'];
            } // 4. Авторизованный пользователь без займов
            elseif ($zaimDate == $defaultZaimDate && !$haveClosedLoans) {
                $blockTypes[] = Faq::BLOCK_TYPES['authorized_no_loans'];
            }

            $faqItems = $this->faq->getFaqByType($blockTypes);
            $faqSections = $this->groupFaqBySection($faqItems);

            $blockGoalId = !empty($faqItems) ? ($faqItems[0]->parent_goal_id ?? null) : null;

            $this->design->assign('faq_sections', $faqSections);
            $this->design->assign('is_user_faq', true);
            $this->design->assign('block_goal_id', $blockGoalId);

            return $this->design->fetch('user_faq.tpl');
        }

        // Обработка /faq/main
        if ($blockKey === 'main') {
            $blockKey = 'public';
        }

        $blockType = Faq::BLOCK_TYPES[$blockKey] ?? Faq::BLOCK_TYPES['public'];
        $faqItems = $this->faq->getFaqByType($blockType);
        $faqSections = $this->groupFaqBySection($faqItems);

        $blockGoalId = !empty($faqItems) ? ($faqItems[0]->parent_goal_id ?? null) : null;

        $this->design->assign('faq_sections', $faqSections);
        $this->design->assign('is_user_faq', false);
        $this->design->assign('block_goal_id', $blockGoalId);

        return $this->design->fetch('faq.tpl');
    }

    private function groupFaqBySection(array $items): array {
        $grouped = [];

        foreach ($items as $item) {
            $sectionId = $item->section_id;

            if (!isset($grouped[$sectionId])) {
                $grouped[$sectionId] = [
                    'section_id' => $sectionId,
                    'section_name' => $item->section_name,
                    'block_yandex_goal_id' => $item->parent_goal_id ?? null,
                    'faqs' => []
                ];
            }

            $grouped[$sectionId]['faqs'][] = $item;
        }

        return array_values($grouped);
    }

    private function renderFaqSection(bool $isUser = false) {
        if ($isUser) {
            $this->jwtAuthValidate();

            if (empty($this->user)) {
                header('Location: ' . $this->config->root_url . '/user/login');
                exit();
            }
        }

        $sectionId = $this->request->get('section_id', 'integer');
        $questionId = $this->request->get('q', 'integer');

        if (!$sectionId) return false;

        $faqItems = $this->faq->getFaqBySectionId($sectionId);
        if (empty($faqItems)) return false;

        $selected = $questionId ? array_filter($faqItems, fn($f) => $f->id == $questionId) : [$faqItems[0]];
        $selectedFaq = reset($selected);

        $this->design->assign('faqs', $faqItems);
        $this->design->assign('section_name', $faqItems[0]->section_name);
        $this->design->assign('selected_question', $selectedFaq->question ?? '');
        $this->design->assign('selected_answer', $selectedFaq->answer ?? '');
        $this->design->assign('is_user_faq', $isUser);

        return $this->design->fetch($isUser ? 'user_faq_section.tpl' : 'faq_section.tpl');
    }
}