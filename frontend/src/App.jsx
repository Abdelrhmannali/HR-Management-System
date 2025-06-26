import React from "react";
import { BrowserRouter } from "react-router-dom";
import RouterComponent from "./router";
import { ToastContainer } from "react-toastify";
import "react-toastify/dist/ReactToastify.css";

function App() {
  return (
    <BrowserRouter>
      <RouterComponent />
      <ToastContainer />
    </BrowserRouter>
  );
}

export default App;
