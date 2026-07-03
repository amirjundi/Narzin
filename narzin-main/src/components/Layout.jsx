import React from "react";
import Navbar from "./includes/Navbar";
import AfterNav from "./includes/AfterNav";
import { Outlet } from "react-router-dom";
import Footer from "./includes/Footer";
import Nav from "./includes/NavTest";
import { ToastContainer } from "react-toastify";
import NavBar from "./New/NavBar";
import HomePopup from "./pages/home/blocks/HomePopup";

const Layout = ({data}) => {
  return (
    <div className="bg-white">
      {/* <Navbar />
      <AfterNav /> */}
        <NavBar data={data} />
        <HomePopup />
      <Outlet />
      <ToastContainer position="top-right" autoClose={3000} />

      <Footer/>
    </div>
  );
};

export default Layout;
