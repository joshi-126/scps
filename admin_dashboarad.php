<?php
include("db.php");
include("header.php");

// Example: Retrieve admin stats
$admin = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM admins LIMIT 1"));
?>
<div class="dashboard-container">

    <!-- Sidebar -->
    <aside class="sidebar">
        <ul>
            <li><img src="assets/images/students.png"> Manage Students</li>
            <li><img src="assets/images/faculty.png"> Manage Faculty</li>
            <li><img src="assets/images/events.png"> Manage Events</li>
            <li><img src="assets/images/lost.png"> View Lost Reports</li>
            <li><img src="assets/images/books.png"> List Old Books</li>
        </ul>
    </aside>

    <!-- Main Body -->
    <main class="main-content">
        <!-- Detail Bar -->
        <section class="detail-bar">
            <h2>Welcome, <?php echo $admin['name']; ?></h2>
        </section>

        <!-- Tracking Bars -->
        <section class="tracking-bars">
            <div class="tracking-box">
                <img src="assets/images/students.png">
                <h3>Students</h3>
                <p><?php echo $admin['students_count']; ?></p>
            </div>

            <div class="tracking-box">
                <img src="assets/images/faculty.png">
                <h3>Faculty</h3>
                <p><?php echo $admin['faculty_count']; ?></p>
            </div>

            <div class="tracking-box">
                <img src="assets/images/lost.png">
                <h3>Lost Reports</h3>
                <p><?php echo $admin['lost_reports']; ?></p>
            </div>

            <div class="tracking-box">
                <img src="assets/images/books.png">
                <h3>Old Books</h3>
                <p><?php echo $admin['old_books']; ?></p>
            </div>
        </section>
    </main>
</div>
<?php include("footer.php"); ?>
