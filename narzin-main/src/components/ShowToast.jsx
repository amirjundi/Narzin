import { toast } from "react-toastify";
import "react-toastify/dist/ReactToastify.css";

const ShowToast = (message, type = "success") => {
  if (type === "success") {
    toast.success(message);
  } else if (type === "error") {
    toast.error(message);
  } else {
    toast.info(message);
  }
};

export default ShowToast;
