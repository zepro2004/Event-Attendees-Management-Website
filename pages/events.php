<?php
/**
 * Events Listing Page
 * 
 * This page displays all available events with filtering options.
 * Users can search and filter events based on various criteria including
 * search terms, location (city/state), and date ranges.
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include '../includes/header.php';
?>

<div class="container">
    <h2>Events</h2>
    
    <!-- Search Form - Provides filtering options for events -->
    <div class="search-container">
        <form id="searchForm" class="search-form">
            <div class="form-row">
                <!-- Text search field for event titles and descriptions -->
                <div class="form-group">
                    <label for="search">Search:</label>
                    <input type="text" id="search" name="search" placeholder="Search events...">
                </div>
                <!-- Location filtering - City -->
                <div class="form-group">
                    <label for="city">City:</label>
                    <input type="text" id="city" name="city" placeholder="Filter by city">
                </div>
                <!-- Location filtering - State/Province -->
                <div class="form-group">
                    <label for="state">State/Province:</label>
                    <input type="text" id="state" name="state" placeholder="Filter by state">
                </div>
            </div>
            
            <div class="form-row">
                <!-- Date filtering options -->
                <div class="form-group">
                    <label for="date">Date:</label>
                    <input type="date" id="date" name="date">
                </div>
                <!-- Month selection dropdown -->
                <div class="form-group">
                    <label for="month">Month:</label>
                    <select id="month" name="month">
                        <option value="">Any month</option>
                        <?php
                        // Generate month options dynamically
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
                <!-- Year selection dropdown -->
                <div class="form-group">
                    <label for="year">Year:</label>
                    <select id="year" name="year">
                        <option value="">Any year</option>
                        <?php
                        // Generate 5 years of options starting with current year
                        $currentYear = date('Y');
                        for ($y = $currentYear; $y <= $currentYear + 5; $y++) {
                            echo "<option value=\"$y\">$y</option>";
                        }
                        ?>
                    </select>
                </div>
            </div>
           
            <!-- Form action buttons -->
            <div class="form-actions">
                <button type="submit" class="btn">Search</button>
                <button type="reset" class="btn secondary">Reset</button>
            </div>
        </form>
    </div>

    <!-- Events List Container - Populated via JavaScript -->
    <div id="eventsList" class="events-list" 
        data-user-id="<?php echo isset($_SESSION['user_id']) ? $_SESSION['user_id'] : '0'; ?>"
        data-page="public">
        <div class="loading">Loading events...</div>
    </div>

    <!-- Login prompt for non-authenticated users -->
    <?php if (!isset($_SESSION['user_id'])): ?>
        <div class="login-prompt">
            <p>Please <a href="login.php">login</a> to manage your events.</p>
        </div>
    <?php endif; ?>
</div>

<!-- Include JavaScript for event listing and filtering functionality -->
<script src="../scripts/events.js"></script>
<?php include '../includes/footer.php'; ?>