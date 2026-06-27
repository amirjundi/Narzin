import React from "react";
import arrow from '../../assets/images/arrow.svg'
import { Link } from "react-router-dom";
import { useTranslation } from "react-i18next";

const PrimaryLink = ({route , text }) => {
    const { t, i18n } = useTranslation();
    const isLTR = i18n.language != "ar";
  return (
    <div>
      <Link
        className="flex items-center decoration-[#225E8A] group"
        to={`/${route}`}
      >
        <span className="text-[#225E8A]">{text}</span>
        <img
          src={arrow}
          className={`${isLTR ? 'rotate-180 group-hover:translate-x-2' : 'group-hover:-translate-x-2'} mx-2 transition-transform duration-300`}
        />
      </Link>
    </div>
  );
};

export default PrimaryLink;
