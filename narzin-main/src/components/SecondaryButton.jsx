import React from 'react'

const SecondaryButton = ({text}) => {
  return (
    <div>
    <button className="flex mx-2 text-[#225E8A] py-[10px] px-[15px] justify-center items-center gap-[7.912px] self-stretch rounded-[10px] bg-[#EAF3F9] shadow-[inset_3.956px_3.956px_12.857px_rgba(255,255,255,0.25),1.978px_0_12.857px_rgba(0,0,0,0.15)] hover:bg-[#d8e8f2] hover:shadow-[inset_3.956px_3.956px_16px_rgba(255,255,255,0.35),2px_0_16px_rgba(0,0,0,0.2)] transition-all">
  {text}
</button>

    </div>
  )
}

export default SecondaryButton