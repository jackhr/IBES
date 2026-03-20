import React from "react";
import ReactDOM from "react-dom/client";
import { BrowserRouter } from "react-router-dom";
import App from "./App";
import "@fortawesome/fontawesome-free/css/all.min.css";
import "flatpickr/dist/flatpickr.min.css";
import "sweetalert2/dist/sweetalert2.min.css";
import "../styles/main.css";
import "../styles/about.css";
import "../styles/faq.css";
import "../styles/contact.css";
import "../styles/taxi.css";
import "../styles/reservation.css";
import "../styles/confirmation.css";

ReactDOM.createRoot(document.getElementById("root")!).render(
  <React.StrictMode>
    <BrowserRouter>
      <App />
    </BrowserRouter>
  </React.StrictMode>
);
