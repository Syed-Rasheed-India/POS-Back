SELECT table_name
FROM information_schema.tables
WHERE table_schema = 'public'
AND table_name IN (
    'companies',
    'currencies',
    'company_subscriptions',
    'payment_methods',
    'subscription_invoices',
    'subscription_invoice_items',
    'subscription_payments',
    'payment_gateways',
    'company_payment_gateways'
)
ORDER BY table_name;
