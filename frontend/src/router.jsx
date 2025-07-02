import React from 'react';
import { Routes, Route } from 'react-router-dom';
import Sidebar from './components/Sidebar';
import Holidayes from './pages/Holidayes/Holidayes';
import Login from './pages/Login/Login';
import Payroll from './pages/Payroll/Payroll';

import DepartmentsPage from "./pages/Departments/Departments";
import AddHrPage from './pages/Hr/AddHrPage';
import UpdateHrPage from './pages/Hr/UpdateHrPage';
import Employees from './pages/Employees/Employees';
import AddEmployee from './pages/Employees/AddEmployee';
import EditEmployee from './pages/Employees/EditEmployee';
import AttendancePage from './pages/Attendance/AttendancePage';
import GeneralSettingForm from './pages/Settings/GeneralSettingForm';
import ForgotPassword from "./pages/Login/ForgotPassword";
import DashboardPage from './pages/DashboardPage/DashboardPage';


  import ResetPassword from './pages/Login/ResetPassword';


export default function RouterComponent() {
  return (
    <Routes>
        <Route path="/Dashboard" element={<DashboardPage/>} />
      
    

      
      {/* Fallback route */}
    </Routes>
  );
}