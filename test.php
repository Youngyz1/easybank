<?php
echo "PHP is working!";
?>

<?php
$stripeKey = getenv('STRIPE_TEST_SECRET');
var_dump($stripeKey);


require __DIR__ . '/vendor/autoload.php';

// Load .env
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Access environment variables
$stripeKey = getenv('STRIPE_TEST_SECRET');
$stripePublic = getenv('STRIPE_TEST_PUBLIC');

echo "PHP is working!\n";
var_dump($stripeKey);
var_dump($stripePublic);
