SELECT
    t.table_name,
    CASE
        WHEN c.table_name IS NOT NULL THEN 'EXISTS'
        ELSE 'MISSING'
    END AS table_status
FROM (
    VALUES
        ('subscription_invoices'),
        ('subscription_invoice_items'),
        ('subscription_payments'),
        ('payment_gateways'),
        ('company_payment_gateways'),
        ('ecr_providers'),
        ('ecr_terminal_mappings'),
        ('ecr_transaction_logs'),
        ('platform_api_clients'),
        ('api_usage_logs'),
        ('platform_notifications'),
        ('company_notifications'),
        ('notification_delivery_logs')
) AS t(table_name)
LEFT JOIN information_schema.tables c
    ON c.table_schema = 'public'
   AND c.table_name = t.table_name
ORDER BY t.table_name;
