<!DOCTYPE html>
<html>

<head>
    <title>Customer Report</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <h1>Customer Report</h1>

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
    $customerName = "";
    $customerPhone = "";
    $customerEmail = "";
    $reportType = "";

    // Check if the form is submitted
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $customerName = $_POST["customer_name"];
        $customerPhone = $_POST["customer_phone"];
        $customerEmail = $_POST["customer_email"];
        $reportType = $_POST["report_type"];

        // Query to retrieve customer data based on user input
        // Query to retrieve customer data based on user input
        $sql = "SELECT customers.id AS customer_id, customers.name AS customer_name, customers.phone AS customer_phone, customers.email AS customer_email, orders.id AS order_id, orders.order_date, orders.product_name
        FROM customers
        INNER JOIN orders ON customers.id = orders.customer_id
        WHERE customers.name = '$customerName' AND customers.phone = '$customerPhone' AND customers.email = '$customerEmail'";



        // Determine the date range based on the selected report type
        if ($reportType == "week") {
            $startDate = date('Y-m-d', strtotime('-1 week'));
            $endDate = date('Y-m-d');
        } else {
            $startDate = date('Y-m-01');
            $endDate = date('Y-m-t');
        }

        // Add date range conditions to the SQL query
        $sql .= " AND orders.order_date BETWEEN '$startDate' AND '$endDate'";

        $result = $conn->query($sql);

        // Display the report
        if ($result->num_rows > 0) {
            $firstRow = $result->fetch_assoc(); // Fetch the first row to get customer info
            $customerID = $firstRow["customer_id"];
            $customerName = $firstRow["customer_name"];
            $customerPhone = $firstRow["customer_phone"];
            $customerEmail = $firstRow["customer_email"];

            echo "<h2>Customer Information</h2>";
            echo "<table border='1'>";
            echo "<tr><th>Customer ID</th><th>Customer Name</th><th>Phone</th><th>Email</th></tr>";
            echo "<tr><td>$customerID</td><td>$customerName</td><td>$customerPhone</td><td>$customerEmail</td></tr>";
            echo "</table>";

            echo "<h2>Purchased Item</h2>";
            echo "<h3>Report Type: " . ucfirst($reportType) . "</h3>";
            echo "<table border='1'>";
            echo "<tr><th>Order ID</th><th>Order Date</th><th>Product Name</th></tr>";

            // Display order details
            $result->data_seek(0); // Reset result pointer to the beginning

            while ($row = $result->fetch_assoc()) {
                echo "<tr><td>" . $row["order_id"] . "</td><td>" . $row["order_date"] . "</td><td>" . $row["product_name"] . "</td></tr>";
            }

            echo "</table>";
        } else {
            echo "No data available for this customer.";
        }
    }
    ?>

    <br>

    <!-- Create a form for user input -->
    <h2>Search for Customer Data</h2>
    <form method="POST" action="">
        <label>Name:</label>
        <input type="text" name="customer_name" required><br>
        <label>Phone:</label>
        <input type="text" name="customer_phone" required><br>
        <label>Email:</label>
        <input type="email" name="customer_email" required><br>
        <label>Report Type:</label>
        <select name="report_type">
            <option value="week">Weekly Report</option>
            <option value="month">Monthly Report</option>
        </select><br>
        <input type="submit" value="Search">
    </form>

    <!-- Add a link to generate the CSV file -->
    <br>
    <a href="generate_csv.php?name=<?php echo $customerName; ?>&phone=<?php echo $customerPhone; ?>&email=<?php echo $customerEmail; ?>&report_type=<?php echo $reportType; ?>">Generate CSV</a>

    <?php
    // Close the database connection
    $conn->close();
    ?>
</body>

</html>