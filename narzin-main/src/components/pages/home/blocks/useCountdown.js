import { useEffect, useState } from "react";

function remaining(endsAtIso, nowMs) {
  const diff = Date.parse(endsAtIso) - nowMs;
  if (Number.isNaN(diff) || diff <= 0) {
    return { days: 0, hours: 0, minutes: 0, seconds: 0, expired: true };
  }
  const totalSeconds = Math.floor(diff / 1000);
  return {
    days: Math.floor(totalSeconds / 86400),
    hours: Math.floor((totalSeconds % 86400) / 3600),
    minutes: Math.floor((totalSeconds % 3600) / 60),
    seconds: totalSeconds % 60,
    expired: false,
  };
}

export function useCountdown(endsAtIso) {
  const [state, setState] = useState(() => remaining(endsAtIso, Date.now()));

  useEffect(() => {
    setState(remaining(endsAtIso, Date.now()));
    const timer = setInterval(() => {
      setState(remaining(endsAtIso, Date.now()));
    }, 1000);
    return () => clearInterval(timer);
  }, [endsAtIso]);

  return state;
}
