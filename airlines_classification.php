<?php
session_start();
include('connect.php');

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

function getInt($key) {
    return isset($_POST[$key]) ? intval($_POST[$key]) : 0;
}

$prediction = "";
$confidence = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // =========================
    // DJANGO API PAYLOAD
    // =========================
    $data = [
        "Gender" => $_POST['Gender'],
        "Customer_Type" => $_POST['Customer_Type'],
        "Type_of_Travel" => $_POST['Type_of_Travel'],
        "Class" => $_POST['Class'],

        "Inflight_wifi_service" => getInt('Inflight_wifi_service'),
        "Departure_Arrival_time_convenient" => getInt('Departure_Arrival_time_convenient'),
        "Ease_of_Online_booking" => getInt('Ease_of_Online_booking'),
        "Gate_location" => getInt('Gate_location'),
        "Food_and_drink" => getInt('Food_and_drink'),
        "Online_boarding" => getInt('Online_boarding'),
        "Seat_comfort" => getInt('Seat_comfort'),
        "Inflight_entertainment" => getInt('Inflight_entertainment'),
        "On_board_service" => getInt('On_board_service'),
        "Leg_room_service" => getInt('Leg_room_service'),
        "Baggage_handling" => getInt('Baggage_handling'),
        "Checkin_service" => getInt('Checkin_service'),
        "Inflight_service" => getInt('Inflight_service'),
        "Cleanliness" => getInt('Cleanliness')
    ];

    // =========================
    // CALL DJANGO API
    // =========================
    $ch = curl_init("http://127.0.0.1:8000/predict/");

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json'
    ]);

    $response = curl_exec($ch);

    if (curl_errno($ch)) {
        die("API Error: " . curl_error($ch));
    }

    curl_close($ch);

    $res = json_decode($response, true);

    if (!$res || !isset($res['prediction'])) {
        die("Invalid API response: " . $response);
    }

    $prediction = $res['prediction'];
    $confidence = $res['confidence'];

    // =========================
    // STORE VARIABLES
    // =========================
    $user_id        = $_SESSION['user_id'];
    $gender         = $_POST['Gender'];
    $customer_type  = $_POST['Customer_Type'];
    $type_of_travel = $_POST['Type_of_Travel'];
    $class          = $_POST['Class'];

    $wifi           = getInt('Inflight_wifi_service');
    $dep_arr        = getInt('Departure_Arrival_time_convenient');
    $ease           = getInt('Ease_of_Online_booking');
    $gate           = getInt('Gate_location');
    $food           = getInt('Food_and_drink');
    $online         = getInt('Online_boarding');
    $seat           = getInt('Seat_comfort');
    $entertainment  = getInt('Inflight_entertainment');
    $service        = getInt('On_board_service');
    $legroom        = getInt('Leg_room_service');
    $baggage        = getInt('Baggage_handling');
    $checkin        = getInt('Checkin_service');
    $inflight       = getInt('Inflight_service');
    $clean          = getInt('Cleanliness');

    // =========================
    // DATABASE INSERT
    // =========================
    $stmt = $conn->prepare("
        INSERT INTO classification 
        (
            user_id, Gender, Customer_Type, Type_of_Travel, Class,
            Inflight_wifi_service,
            Departure_Arrival_time_convenient,
            Ease_of_Online_booking,
            Gate_location,
            Food_and_drink,
            Online_boarding,
            Seat_comfort,
            Inflight_entertainment,
            On_board_service,
            Leg_room_service,
            Baggage_handling,
            Checkin_service,
            Inflight_service,
            Cleanliness,
            target,
            confidence
        )
        VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)
    ");

    // =========================
    // FIXED TYPE STRING
    // i  = user_id
    // ssss = gender, customer_type, type_of_travel, class
    // iiiiiiiiiiiiii = 14 integer ratings
    // s  = prediction (target)
    // d  = confidence (float)
    // Total: 21 characters = 21 variables ✓
    // =========================
    $stmt->bind_param(
        "issssiiiiiiiiiiiiiisd",  // 1i + 4s + 14i + 1s + 1d = 21 ✓
        $user_id,
        $gender,
        $customer_type,
        $type_of_travel,
        $class,
        $wifi,          // i 1
        $dep_arr,       // i 2
        $ease,          // i 3
        $gate,          // i 4
        $food,          // i 5
        $online,        // i 6
        $seat,          // i 7
        $entertainment, // i 8
        $service,       // i 9
        $legroom,       // i 10
        $baggage,       // i 11
        $checkin,       // i 12
        $inflight,      // i 13
        $clean,         // i 14
        $prediction,    // s
        $confidence     // d
    );

    if (!$stmt->execute()) {
        die("DB Error: " . $stmt->error);
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Airline Satisfaction Prediction</title>
    <link rel="stylesheet" href="css/classify.css">
</head>

<body>

<div class="container">

    <div class="header">
        <h2>Airline Satisfaction Prediction</h2>
        <a href="user_dashboard.php" class="btn">Back</a>
    </div>

    <div class="card">

        <form method="POST">

            <select name="Gender" required>
                <option value="">Gender</option>
                <option value="Male">Male</option>
                <option value="Female">Female</option>
            </select>

            <select name="Customer_Type" required>
                <option value="">Customer Type</option>
                <option value="Loyal Customer">Loyal Customer</option>
                <option value="disloyal Customer">Disloyal Customer</option>
            </select>

            <select name="Type_of_Travel" required>
                <option value="">Type of Travel</option>
                <option value="Business travel">Business travel</option>
                <option value="Personal Travel">Personal Travel</option>
            </select>

            <select name="Class" required>
                <option value="">Class</option>
                <option value="Eco">Eco</option>
                <option value="Eco Plus">Eco Plus</option>
                <option value="Business">Business</option>
            </select>

            <?php
            function rating($name, $label) {
                echo "<select name='$name' required>";
                echo "<option value=''>$label</option>";
                for ($i = 0; $i <= 5; $i++) {
                    echo "<option value='$i'>$i</option>";
                }
                echo "</select>";
            }

            rating("Inflight_wifi_service",              "Inflight WiFi Service");
            rating("Departure_Arrival_time_convenient",  "Departure/Arrival Convenience");
            rating("Ease_of_Online_booking",             "Ease of Online Booking");
            rating("Gate_location",                      "Gate Location");
            rating("Food_and_drink",                     "Food and Drink");
            rating("Online_boarding",                    "Online Boarding");
            rating("Seat_comfort",                       "Seat Comfort");
            rating("Inflight_entertainment",             "Inflight Entertainment");
            rating("On_board_service",                   "On-board Service");
            rating("Leg_room_service",                   "Leg Room Service");
            rating("Baggage_handling",                   "Baggage Handling");
            rating("Checkin_service",                    "Checkin Service");
            rating("Inflight_service",                   "Inflight Service");
            rating("Cleanliness",                        "Cleanliness");
            ?>

            <button type="submit">Predict</button>

        </form>

        <?php if ($prediction): ?>
            <div class="result">
                <h3>Result: <?php echo htmlspecialchars($prediction); ?></h3>
                <p>Confidence: <?php echo round($confidence * 100, 2); ?>%</p>
            </div>
        <?php endif; ?>

    </div>

</div>

</body>
</html>