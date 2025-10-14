<?php
error_reporting(0);
ini_set('display_errors', 0);

session_start();
chdir('..');

require_once __DIR__ . '/../api/Simpla.php';

class ChooseCard extends Simpla
{
    public function run()
    {
        $this->validateCardId();
        $this->validateOrderId();

        $user = $this->getUser();
        $userCards = $this->getUserCards($user);

        $userOrders = $this->getUserOrders($user);
        $selectedCard = $this->getSelectedCard($userCards);

        $this->checkLoanForCard($userOrders, $selectedCard);
        $currentOrder = $this->getCurrentOrder($userOrders);
        $oldCard = $this->getOldCard($userCards, $currentOrder);

        $this->updateOrderCardId($currentOrder, $selectedCard);
        $this->updateContractCardId($currentOrder, $selectedCard);

        $this->addClientLogging($currentOrder, $selectedCard);
        $this->addOrderLogging($currentOrder, $selectedCard, $oldCard);

        $this->request->json_output(['result' => 'success']);
    }

    /**
     * @return int
     */
    private function getCardIdFromRequest(): int
    {
        return $this->request->post('card_id', 'integer');
    }

    /**
     * @return int
     */
    private function getOrderIdFromRequest(): int
    {
        return $this->request->post('order_id', 'integer');
    }

    /**
     * @return void
     */
    private function validateCardId(): void
    {
        if (empty($this->getCardIdFromRequest())) {
            $this->request->json_output(['error' => 'Карта не найдена. Если ошибка сохраняется, обратитесь в техническую поддержку']);
        }
    }

    /**
     * @return void
     */
    private function validateOrderId(): void
    {
        if (empty($this->getOrderIdFromRequest())) {
            $this->request->json_output(['error' => 'Заем не найден. Если ошибка сохраняется, обратитесь в техническую поддержку']);
        }
    }

    /**
     * @return stdClass
     */
    private function getUser(): stdClass
    {
        $user = $this->users->get_user((int)$_SESSION['user_id']);

        if (empty($user)) {
            $this->request->json_output(['error' => 'Пользователь не найден. Если ошибка сохраняется, обратитесь в техническую поддержку']);
        }

        return $user;
    }

    /**
     * @param array $userCards
     * @return stdClass
     */
    private function getSelectedCard(array $userCards): stdClass
    {
        foreach ($userCards as $card) {
            if ((int)$card->id === $this->getCardIdFromRequest()) {
                return $card;
            }
        }

        $this->request->json_output(['error' => 'Карта не найдена']);
        exit();
    }

    /**
     * @param stdClass $user
     * @return array
     */
    private function getUserOrders(stdClass $user): array
    {
        $userOrders = $this->orders->get_orders([
            'user_id' => $user->id
        ]);

        if (empty($userOrders)) {
            $this->request->json_output(['error' => 'Заявки не найдены']);
        }

        return $userOrders;
    }

    /**
     * Проверяет, относится ли карта к базовой организации и был ли ранее на данную карту выдан заем
     *
     * @param array $userOrders
     * @param stdClass $selectedCard
     * @return void
     */
    private function checkLoanForCard(array $userOrders, stdClass $selectedCard): void
    {
        if ((int)$selectedCard->organization_id !== $this->organizations->get_base_organization_id()) {
            $this->request->json_output(['error' => 'Выбор данной карты невозможен']);
        }

        foreach ($userOrders as $order) {
            if (((int)$order->status === $this->orders::STATUS_CONFIRMED && $order->card_id === $selectedCard->id) || (int)$order->status === $this->orders::STATUS_APPROVED) {
                return;
            }
        }

        $this->request->json_output(['error' => 'Карта не соответствует требуемым условиям']);
    }

    /**
     * @param array $userOrders
     * @return stdClass
     */
    private function getCurrentOrder(array $userOrders): stdClass
    {
        $currentOrder = null;
        foreach ($userOrders as $order) {
            if ((int)$order->id === $this->getOrderIdFromRequest()) {
                $currentOrder = $order;
                break;
            }
        }

        if ($currentOrder === null) {
            $this->request->json_output(['error' => 'Заем не найден']);
        }

        if (empty($currentOrder->have_close_credits)) {
            $this->request->json_output(['error' => 'Выбор карты невозможен']);
        }

        return $currentOrder;
    }

    /**
     * @param array $userCards
     * @param stdClass $currentOrder
     * @return stdClass|null
     */
    private function getOldCard(array $userCards, stdClass $currentOrder): ?stdClass
    {
        foreach ($userCards as $card) {
            if ($card->id === $currentOrder->card_id) {
                return $card;
            }
        }

        return null;
    }

    /**
     * @param stdClass $currentOrder
     * @param stdClass $selectedCard
     * @return void
     */
    private function updateOrderCardId(stdClass $currentOrder, stdClass $selectedCard): void
    {
        if ((int)$currentOrder->status === $this->orders::STATUS_APPROVED) {
            $this->updateApprovedOrder($currentOrder, $selectedCard);
        } else {
            $this->orders->update_order($currentOrder->id, [
                'card_id' => $selectedCard->id,
            ]);
        }
    }

    private function updateApprovedOrder(stdClass $currentOrder, stdClass $selectedCard): void
    {
        $this->orders->update_order($currentOrder->id, [
            'card_id' => $selectedCard->id,
            'status' => $this->orders::STATUS_NEW,
            'manager_id' => null,
        ]);

        $this->order_data->set($currentOrder->id, 'is_new_card_linked', 1);

        $this->soap->update_status_1c(
            $currentOrder->id_1c,
            $this->orders::ORDER_UPDATE_1C_STATUS_CONSIDERED,
            $this->managers->get_manager($this->managers::MANAGER_SYSTEM_ID)->name_1c,
            $currentOrder->amount,
            $currentOrder->percent,
            'Привязка новой карты к займу',
            0,
            $currentOrder->selected_period ?: $currentOrder->period
        );
    }

    /**
     * @param stdClass $currentOrder
     * @param stdClass $selectedCard
     * @return void
     */
    private function updateContractCardId(stdClass $currentOrder, stdClass $selectedCard): void
    {
        if (!empty($currentOrder->contract_id)) {
            $this->contracts->update_contract($currentOrder->contract_id, [
                'card_id' => $selectedCard->id,
            ]);
        }
    }

    /**
     * Добавляет логирования на страницу клиента
     *
     * @param stdClass $currentOrder
     * @param stdClass $selectedCard
     * @return void
     */
    private function addClientLogging(stdClass $currentOrder, stdClass $selectedCard): void
    {
        $this->comments->add_comment([
            'manager_id' => $this->managers::MANAGER_SYSTEM_ID,
            'user_id' => $selectedCard->user_id,
            'order_id' => $currentOrder->id,
            'block' => 'card_change',
            'text' => 'Клиент выбрал карту ' . $selectedCard->pan,
            'created' => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Добавляет логирование на страницу заявки
     *
     * @param stdClass $currentOrder
     * @param stdClass $selectedCard
     * @param stdClass $oldCard
     * @return void
     */
    private function addOrderLogging(stdClass $currentOrder, stdClass $selectedCard, stdClass $oldCard): void
    {
        $this->changelogs->add_changelog([
            'manager_id' => $this->managers::MANAGER_SYSTEM_ID,
            'created' => date('Y-m-d H:i:s'),
            'type' => 'card_change',
            'old_values' => 'Номер карты: ' . $oldCard->pan,
            'new_values' => 'Номер карты: ' . $selectedCard->pan,
            'order_id' => $currentOrder->id,
            'user_id' => $currentOrder->user_id,
        ]);
    }

    /**
     * @param stdClass $user
     * @return array
     */
    private function getUserCards(stdClass $user): array
    {
        $userCards = $this->best2pay->get_cards([
            'deleted' => 0,
            'deleted_by_client' => 0,
            'user_id' => $user->id,
        ]);

        if (empty($userCards)) {
            $this->request->json_output(['error' => 'Карты не найдены']);
        }

        if (count($userCards) <= 1) {
            $this->request->json_output(['error' => 'Прикреплена только 1 карта']);
        }

        return $userCards;
    }
}

$chooseCard = new ChooseCard();
$chooseCard->run();
