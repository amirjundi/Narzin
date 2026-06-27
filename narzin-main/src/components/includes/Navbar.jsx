import React from "react";
import Logo from "../Logo";
import PrimaryButton from "../PrimaryButton";
import SecondaryButton from "../SecondaryButton";
import card from "../../assets/images/card.svg";

const Navbar = () => {
  return (
    <div className="p-5 flex items-center justify-between">
      <Logo />
      <div className="w-[75%]">
      <input type="text" placeholder="Type here" className="input input-bordered w-full " />

      </div>
      <div className="">

        <button className="flex">
            card <img src={card}/>
        </button>

      </div>
      <div className=""></div>
      <div className="flex items-center">
        <PrimaryButton text="انشاء حساب"/>
        <SecondaryButton text="تسجيل دخول"/>
      </div>
    </div>
  );
};

export default Navbar;
