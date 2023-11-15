<?php
// Database connection settings
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "customer_report_db";

// Create a database connection
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Initialize variables to store user input
$customerName = $_GET["name"];
$customerPhone = $_GET["phone"];
$customerEmail = $_GET["email"];
$reportType = $_GET["report_type"];

// Query to retrieve customer data based on user input
$sql = "SELECT customers.id AS customer_id, customers.name AS customer_name, customers.phone AS customer_phone, customers.email AS customer_email
        FROM customers
        WHERE customers.name = '$customerName' AND customers.phone = '$customerPhone' AND customers.email = '$customerEmail'";

$result = $conn->query($sql);

// Initialize CSV data
$csvData = "";

// Check if customer data is available
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $csvData .= "Customer Report\n";
        $csvData .= "Customer Information\n";
        $csvData .= "Customer ID,Customer Name,Phone,Email\n";
        $csvData .= "{$row['customer_id']},{$row['customer_name']},{$row['customer_phone']},{$row['customer_email']}\n";
        $csvData .= "Report Type: " . ucfirst($reportType) . "\n";
    }
}

// Query to retrieve order details based on user input
$orderSql = "SELECT orders.id AS order_id, orders.order_date, orders.product_name
        FROM orders
        WHERE orders.customer_id IN (SELECT id FROM customers WHERE name = '$customerName' AND phone = '$customerPhone' AND email = '$customerEmail')";

// Determine the date range based on the selected report type
if ($reportType == "week") {
    $startDate = date('Y-m-d', strtotime('-1 week'));
    $endDate = date('Y-m-d');
} else {
    $startDate = date('Y-m-01');
    $endDate = date('Y-m-t');
}

// Add date range conditions to the SQL query
$orderSql .= " AND orders.order_date BETWEEN '$startDate' AND '$endDate'";

$orderResult = $conn->query($orderSql);

// Check if order details are available
if ($orderResult->num_rows > 0) {
    $csvData .= "Order ID,Order Date,Product Name\n";
    while ($row = $orderResult->fetch_assoc()) {
        $csvData .= "{$row['order_id']},{$row['order_date']},{$row['product_name']}\n";
    }
}

// Export the report to a CSV file
$filename = "customer_report_$reportType.csv";

// Set appropriate headers for file download
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="' . $filename . '"');

// Create a file handle for output
$file = fopen('php://output', 'w');

if ($file) {
    // Write the CSV data to the output
    fwrite($file, $csvData);

    // Close the file handle
    fclose($file);
} else {
    echo "Failed to create the CSV file. Please try again later.";
}

// Close the database connection
$conn->close();
?>
