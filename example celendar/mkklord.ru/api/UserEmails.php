<?php

require_once 'Simpla.php';

/**
 * Класс для работы с доп. почтами пользователя.
 */
class UserEmails extends Simpla
{
    const SOURCE_COMPLAINT_EMAIL = 'COMPLAINT_EMAIL';
    const SOURCE_FEEDBACK_EMAIL = 'FEEDBACK_EMAIL';
    const SOURCE_USER_TICKET_EMAIL = 'USER_TICKET_EMAIL';

    /**
     * Синхронизирует почты с CRM и 1C из формы жалобы ЛК
     * @param object $user
     * @param string $email
     * @param string $source
     * @return void
     */
    public function syncEmail(object $user, string $email, string $source): void
    {
        $this->syncCrm($user, $email, $source);
        $this->sync1c($user->uid, $email);
    }

    /**
     * Отправляет почту в бд CRM
     * @param object $user
     * @param string $email
     * @param string $source
     * @return void
     */
    private function syncCrm(object $user, string $email, string $source): void
    {
        if (empty($user->email)) {
            $this->users->update_user($user->id, ['email' => $email]);
            return;
        }

        if ($user->email === $email) {
            // Это основной email
            return;
        }

        $usersWithSameEmail = $this->getUsersWithSameEmail($email);
        foreach ($usersWithSameEmail as $otherUserId) {
            if ((int)$otherUserId === (int)$user->id) {
                // Этот доп.email уже добавлен
                return;
            }
        }

        $this->add([
            'user_id' => $user->id,
            'email' => $email,
            'source' => $source
        ]);
    }

    /**
     * Отправляет email в 1С
     * @param string $userUid
     * @param string $email
     * @return void
     */
    private function sync1c(string $userUid, string $email): void
    {
        $this->soap->sendAdditionalEmail($userUid, $email);
    }

    /**
     * Поиск пользователей с указанной доп. почтой
     * @param string $email
     * @return array
     */
    public function getUsersWithSameEmail(string $email): array
    {
        $this->db->query($this->db->placehold('SELECT user_id FROM __user_emails WHERE email = ?', $email));

        return $this->db->results('user_id') ?? [];
    }

    /**
     * Добавление доп. почты в бд CRM
     * @param array $row
     * @return int
     */
    public function add(array $row): int
    {
        $this->db->query($this->db->placehold('INSERT INTO __user_emails SET ?%', $row));

        return $this->db->insert_id();
    }
}