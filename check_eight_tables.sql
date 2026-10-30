SELECT t.table_name,
       CASE WHEN c.table_name IS NULL THEN 'MISSING' ELSE 'EXISTS' END AS status
FROM (VALUES
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
