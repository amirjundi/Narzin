import React from 'react'
import logo from '../assets/images/logo.svg';
import logoText from '../assets/images/logo-text.svg';

const LogoWithText = () => {
  return (
    <div className='flex w-[40px] items-center flex-col	 justify-center'>
      <img src={logo} alt='logo' />
      <img className='mt-2' src={logoText} alt='logo-text' />
    </div>
  )
}

export default Logo