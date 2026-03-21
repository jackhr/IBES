import React from "react";
import ReactDOM from "react-dom/client";
import { BrowserRouter } from "react-router-dom";
import App from "./App";
import "flatpickr/dist/flatpickr.min.css";
import "sweetalert2/dist/sweetalert2.min.css";
import "./pages/HomePage/HomePage.scss";

ReactDOM.createRoot(document.getElementById("root")!).render(
  <React.StrictMode>
    <BrowserRouter>
      <App />
    </BrowserRouter>
  </React.StrictMode>
);
