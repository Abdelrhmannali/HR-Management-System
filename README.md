HRMS - Human Resources Management System
A modern web-based HR management system built to streamline employee, attendance, payroll, and department operations. Designed for efficiency, clarity, and ease of use.


 Features
       Authentication & Role-based Access
      Admin, HR, and Employee login with protected routes.
       Employee Management
      Add/Edit/Delete employees
      Assign departments and roles
      Upload profile pictures
 Attendance Tracking
      Daily check-in / check-out
      Edit or delete attendance logs
      Filter by date, employee, or status
      Export as PDF/CSV
 Payroll Management
      Auto-calculate salaries based on attendance
      Exclude weekends and holidays
      Monthly payroll summary and details
 Department & Holidays
    Manage departments
    Add official holidays
    View holidays per month
 General Settings
    Customize weekends
    Set base salary per department










































    
Installation
bashCopyEdit# Clone the repogit clone -b  https://github.com/Abdelrhmannali/HR-Management-System.git
# Frontend setup cd frontend
npm install
npm run dev
# Backend setup cd backend
composer install
php artisan migrate --seed
php artisan serve

 Team
  Abdelrhman Ali Ali abohassan 
  zeyad Ashraf Elmalky
  Donia Ebrahim Ghazal
  Aya Hammdy Bandary
  Alaa essam

