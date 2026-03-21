import Swal from "sweetalert2";

const brandColor = "#c9561c";

export function showSuccessAlert(title: string, text: string): Promise<unknown> {
  return Swal.fire({
    icon: "success",
    title,
    text,
    confirmButtonColor: brandColor
  });
}

export function showErrorAlert(title: string, text: string): Promise<unknown> {
  return Swal.fire({
    icon: "error",
    title,
    text,
    confirmButtonColor: brandColor
  });
}
