<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
} 
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Events | Event Planning System</title>
    <link rel="stylesheet" href="../assets/styles/styles.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>

    <div class="container">
        <h2>Events</h2>
        
        <!-- Search and Filter Section -->
    <div class="search-container">
        <form id="searchForm" class="search-form">
            <div class="form-row">
                <div class="form-group">
                    <label for="search">Search:</label>
                    <input type="text" id="search" name="search" placeholder="Search events...">
                </div>
                <div class="form-group">
                    <label for="city">City:</label>
                    <input type="text" id="city" name="city" placeholder="Filter by city">
                </div>
                <div class="form-group">
                    <label for="state">Province:</label>
                    <input type="text" id="state" name="state" placeholder="Province/State">
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="postal_code">Postal Code:</label>
                    <input type="text" id="postal_code" name="postal_code" placeholder="Postal/ZIP code">
                </div>
                <div class="form-group">
                    <label for="year">Year:</label>
                    <select id="year" name="year">
                        <option value="">Any year</option>
                        <?php 
                        $currentYear = date('Y');
                        for ($i = $currentYear; $i <= $currentYear + 5; $i++) {
                            echo "<option value=\"$i\">$i</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="month">Month:</label>
                    <select id="month" name="month">
                        <option value="">Any month</option>
                        <?php
                        $months = [
                            1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April',
                            5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August',
                            9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December'
                        ];
                        foreach ($months as $num => $name) {
                            echo "<option value=\"$num\">$name</option>";
                        }
                        ?>
                    </select>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="max_attendees">Max Attendees:</label>
                    <select id="max_attendees" name="max_attendees">
                        <option value="">Any size</option>
                        <option value="10">Small (≤ 10)</option>
                        <option value="50">Medium (≤ 50)</option>
                        <option value="100">Large (≤ 100)</option>
                        <option value="500">Very Large (≤ 500)</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="date">Specific Date:</label>
                    <input type="date" id="date" name="date">
                </div>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn">Search</button>
                <button type="reset" class="btn secondary">Reset</button>
            </div>
        </form>
    </div>

        <!-- Events List with user ID data attribute -->
        <div id="eventsList" class="events-list" 
            data-user-id="<?php echo isset($_SESSION['user_id']) ? $_SESSION['user_id'] : '0'; ?>"
            data-page="public">
            <!-- Events will be loaded here dynamically -->
            <div class="loading">Loading events...</div>
        </div>

        <?php if (!isset($_SESSION['user_id'])): ?>
            <div class="login-prompt">
                <p>Please <a href="login.php">login</a> to manage your events.</p>
            </div>
        <?php endif; ?>
    </div><!-- Container closing tag -->

    <!-- Include the external JavaScript file -->
    <script src="../scripts/events.js"></script>

    <?php include '../includes/footer.php'; ?>
</body>
</html>