<?php
include 'components/connect.php';

$id = create_unique_id(); // Generate a unique order ID
$user_id = $_COOKIE['user_id']; // User ID from cookie or session

$name = isset($_POST['name']) ? filter_var($_POST['name'], FILTER_SANITIZE_STRING) : '';
$number = isset($_POST['number']) ? filter_var($_POST['number'], FILTER_SANITIZE_STRING) : '';
$email = isset($_POST['email']) ? filter_var($_POST['email'], FILTER_SANITIZE_EMAIL) : '';
$address = isset($_POST['address']) ? filter_var($_POST['address'], FILTER_SANITIZE_STRING) : '';
$address_type = isset($_POST['address_type']) ? filter_var($_POST['address_type'], FILTER_SANITIZE_STRING) : '';
$method = isset($_POST['method']) ? filter_var($_POST['method'], FILTER_SANITIZE_STRING) : '';
$product_id = isset($_POST['product_id']) ? filter_var($_POST['product_id'], FILTER_SANITIZE_STRING) : '';
$price = isset($_POST['price']) ? filter_var($_POST['price'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION) : 0.00; // Set default to 0.00
$qty = isset($_POST['qty']) ? filter_var($_POST['qty'], FILTER_SANITIZE_NUMBER_INT) : 0; // Set default to 0

// Now you can prepare and execute your SQL insert statement
// ...

// Prepare the insert statement
$insert_order = $conn->prepare("INSERT INTO `orders` (id, user_id, name, number, email, address, address_type, method, product_id, price, qty, status, date) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");

// Ensure you add a status field with a default value
$status = 'pending'; // or whatever the default should be

// Execute the insert statement with the gathered data
try {
    $insert_order->execute([$id, $user_id, $name, $number, $email, $address, $address_type, $method, $product_id, $price, $qty, $status]);
    echo "Order placed successfully!";
} catch (PDOException $e) {
    echo "Error placing order: " . $e->getMessage();
}



?>


<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>My Orders</title>

   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.1/css/all.min.css">

   <link rel="stylesheet" href="css/style.css">

</head>
<body>
   
<?php include 'components/header.php'; ?>

<section class="orders">

   <h1 class="heading">my orders</h1>

   <div class="box-container">

   <?php
      $select_orders = $conn->prepare("SELECT * FROM `orders` WHERE user_id = ? ORDER BY date DESC");
      $select_orders->execute([$user_id]);
      if($select_orders->rowCount() > 0){
         while($fetch_order = $select_orders->fetch(PDO::FETCH_ASSOC)){
            $select_product = $conn->prepare("SELECT * FROM `products` WHERE id = ?");
            $select_product->execute([$fetch_order['product_id']]);
            if($select_product->rowCount() > 0){
               while($fetch_product = $select_product->fetch(PDO::FETCH_ASSOC)){
   ?>
   <div class="box" <?php if($fetch_order['status'] == 'canceled'){echo 'style="border:.2rem solid red";';}; ?>>
      <a href="view_order.php?get_id=<?= $fetch_order['id']; ?>">
         <p class="date"><i class="fa fa-calendar"></i><span><?= $fetch_order['date']; ?></span></p>
         <img src="uploaded_files/<?= $fetch_product['image']; ?>" class="image" alt="">
         <h3 class="name"><?= $fetch_product['name']; ?></h3>
         <p class="price"><i class="fas fa-indian-rupee-sign"></i> <?= $fetch_order['price']; ?> x <?= $fetch_order['qty']; ?></p>
         <p class="status" style="color:<?php if($fetch_order['status'] == 'delivered'){echo 'green';}elseif($fetch_order['status'] == 'canceled'){echo 'red';}else{echo 'orange';}; ?>"><?= $fetch_order['status']; ?></p>
      </a>
   </div>
   <?php
            }
         }
      }
   }else{
      echo '<p class="empty">no orders found!</p>';
   }
   ?>

   </div>

</section>














<script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.2/sweetalert.min.js"></script>

<script src="js/script.js"></script>

<?php include 'components/alert.php'; ?>

</body>
</html>