# Campus TaskHub 🎓🛠️

> A hyperlocal peer-to-peer student service marketplace designed to help university students earn income through small errands and reduce financial burdens.

---

## 📖 About The Project

**Campus TaskHub** is a platform built specifically for the university ecosystem. It connects students who need help with everyday errands to peers who are willing to complete them for a small fee. By keeping the marketplace hyperlocal to the campus, it ensures convenience, trust, and quick turnarounds, while providing a practical way for students to supplement their income.

### 🎯 Goals
*   **Reduce Student Financial Burden:** Create flexible, peer-to-peer earning opportunities for students between classes.
*   **Hyperlocal Convenience:** Connect peers within the same campus or dormitory area for incredibly fast task completion.
*   **Community Building:** Foster a helpful, reliable, and collaborative campus environment.

---

## 👥 The Team

This project is being collaboratively developed by:
*   Asyraf
*   Afiq
*   Qistina
*   Damia
*   Ahmad
*   Aniq

---

## 🛠️ Built With

*   **Frontend:** HTML, CSS, JavaScript
*   **Backend:** PHP
*   **Database:** MySQL
*   **Environment:** XAMPP (Apache Web Server)
*   **Design/Styling:** Figma converted to custom CSS

---

## 🚀 Getting Started

Follow these instructions to set up the project locally for development and testing using XAMPP.

### Prerequisites
*   **XAMPP** (Includes Apache and MySQL)
*   **Git**

### Installation

1.  **Clone the repository into your XAMPP folder:**
    Navigate to your XAMPP `htdocs` directory (usually `C:\xampp\htdocs` on Windows or `/Applications/XAMPP/htdocs` on Mac) and run:
    ```bash
    git clone [https://github.com/yourusername/CAMPUS_TASKHUB.git](https://github.com/yourusername/CAMPUS_TASKHUB.git)
    ```

2.  **Start your local server:**
    Open the XAMPP Control Panel and start both the **Apache** and **MySQL** modules.

3.  **Set up the Database:**
    * Open your web browser and go to `http://localhost/phpmyadmin`
    * Create a new database named `campus_taskhub` (or your preferred name).
    * Import the database schema (e.g., `database.sql`) provided in the project folder into your new database.

4.  **Configure Database Connection:**
    * Open your project files and locate the database connection file (e.g., `db_connect.php` or `config.php`).
    * Update it with your local MySQL credentials. (By default in XAMPP, the username is `root` and the password is left blank).

5.  **Run the application:**
    Open your web browser and navigate to:
    `http://localhost/CAMPUS_TASKHUB`

---

## 🤝 Contributing

Contributions, issues, and feature requests are welcome! 

1. Fork the Project
2. Create your Feature Branch (`git checkout -b feature/NewMarketplaceFeature`)
3. Commit your Changes (`git commit -m 'Add some NewMarketplaceFeature'`)
4. Push to the Branch (`git push origin feature/NewMarketplaceFeature`)
5. Open a Pull Request
