import os

# Define the directory and a mapping of old to new filenames
dir_path = "FastFinance/files/docs"
file_mappings = {
    "1. Выписка из государственного реестра МФО ЦБ РФ.pdf": "1_extract_from_the_state_register_of_MFOs_of_the_Central_Bank_of_the_Russian_Federation.pdf",
    "10. политика конфиденциальности.pdf": "10_privacy_policy.pdf",
    "11. порядок рассмотрения обращений получателей финансовых услуг.pdf": "11_procedure_for_considering_appeals_from_recipients_of_financial_services.pdf",
    "12. базовый стандарт защиты прав и интересов получателей финансовых услуг.pdf": "12_basic_standard_for_the_protection_of_the_rights_and_interests_of_recipients_of_financial_services.pdf",
    "13. базовый стандарт по управлению рисками микрофинансовых организаций.pdf": "13_basic_standard_for_risk_management_of_microfinance_organizations.pdf",
    "14. базовый стандарт совершения МФО операций на финансовом рынке.pdf": "14_basic_standard_for_MFOs_to_conduct_operations_in_the_financial_market.pdf",
    "15. закон РФ от 07.02.1992 № 2300-1 'О защите прав потребителей'.pdf": "15_law_of_the_Russian_Federation_of_07_02_1992_No_2300_1_On_the_protection_of_consumer_rights.pdf",
    "16. Информационная брошюра Банка России об МФО.pdf": "16_information_brochure_of_the_Bank_of_Russia_on_MFOs.pdf",
    "17. Информация о подаче обращения в адрес ФУ.pdf": "17_information_on_submitting_an_appeal_to_the_FU.pdf",
    "18. Информация о рисках доступа к защищаемой информации.pdf": "18_information_on_the_risks_of_access_to_protected_information.pdf",
    "19. Оферта об использовании процессингового центра BEST2PAY.pdf": "19_offer_on_the_use_of_the_BEST2PAY_processing_center.pdf",
    "20. Политика безопасности платежей BEST2PAY.pdf": "20_BEST2PAY_payment_security_policy.pdf",
    "21. Памятка Банка России о кредитных каникулах для участников СВО.pdf": "21_memo_from_the_Bank_of_Russia_on_credit_holidays_for_participants_in_the_SMO.pdf",
    "22. информация о кредитных каникулах 353-ФЗ.pdf": "22_information_on_credit_holidays_353_FZ.pdf",
    "23. информация о кредитных каникулах 377-ФЗ.pdf": "23_information_on_credit_holidays_377_FZ.pdf",
    "24. перечень третьих лиц, которым передаются пользовательские данные.pdf": "24_list_of_third_parties_to_whom_user_data_is_transferred.pdf",
    "25. ссылки на страницы сайтов, используемых для привлечения клиентов.pdf": "25_links_to_the_pages_of_sites_used_to_attract_customers.pdf",
    "26. Информация для заемщиков о самозапрете.pdf": "26_information_for_borrowers_about_self_prohibition.pdf",
    "27. информация об условиях предоставления займов.pdf": "27_information_on_the_terms_of_granting_loans.pdf",
    "3. Свидетельство ИНН.pdf": "3_TIN_certificate.pdf",
    "4. Устав.pdf": "4_charter.pdf",
    "5. политика обработки и хранения персональных данных.pdf": "5_personal_data_processing_and_storage_policy.pdf",
    "6. соглашение об использовании аналога собственноручной подписи.pdf": "6_agreement_on_the_use_of_an_analogue_of_a_holographic_signature.pdf",
    "7. общие условия договора займа.pdf": "7_general_terms_of_the_loan_agreement.pdf",
    "8. правила предоставления займов.pdf": "8_rules_for_granting_loans.pdf",
    "9. информация для получателей финансовых услуг.pdf": "9_information_for_recipients_of_financial_services.pdf",
    "режим работы и обособленные подразделения.pdf": "work_schedule_and_subdivisions.pdf"
}

# Rename the files
for old_name, new_name in file_mappings.items():
    old_path = os.path.join(dir_path, old_name)
    new_path = os.path.join(dir_path, new_name)
    if os.path.exists(old_path):
        os.rename(old_path, new_path)
        print(f"Renamed '{old_name}' to '{new_name}'")
    else:
        print(f"File '{old_name}' not found.")

print("File renaming complete.")