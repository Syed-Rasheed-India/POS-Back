SELECT
    table_name,
    column_name,
    data_type,
    is_nullable,
    column_default
FROM information_schema.columns
WHERE table_schema = 'public'
AND table_name IN (
    'subscription_invoices',
    'subscription_invoice_items',
    'subscription_payments',
    'payment_gateways',
    'company_payment_gateways'
)
ORDER BY table_name, ordinal_position;
