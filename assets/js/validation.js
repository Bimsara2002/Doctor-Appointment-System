$(document).ready(function () {

  // Set min date = today
  const today = new Date().toISOString().split("T")[0];
  $("#appointment_date").attr("min", today);

  // Phone: only digits, max 10
  $("#phone").on("input", function () {
    this.value = this.value.replace(/\D/g, "").slice(0, 10);
  });

  // Helper functions
  function setError(idInput, idErr, msg) {
    $(idErr).text(msg);
    $(idInput).css("border-color", "#ef4444");
  }

  function clearError(idInput, idErr) {
    $(idErr).text("");
    $(idInput).css("border-color", "rgba(255,255,255,.18)");
  }

  // Live clear errors
  $("#patient_name").on("input", () => clearError("#patient_name", "#err_patient"));
  $("#phone").on("input", () => clearError("#phone", "#err_phone"));
  $("#email").on("input", () => clearError("#email", "#err_email"));
  $("#appointment_time").on("change", () => clearError("#appointment_time", "#err_time"));
  $("#reason").on("input", () => clearError("#reason", "#err_reason"));

  // Reload page when doctor changes
  $("#doctor_id").on("change", function () {

    clearError("#doctor_id", "#err_doctor");

    const docId = $(this).val();

    const name = encodeURIComponent($("#patient_name").val() || "");
    const phone = encodeURIComponent($("#phone").val() || "");
    const email = encodeURIComponent($("#email").val() || "");
    const date = encodeURIComponent($("#appointment_date").val() || "");
    const reason = encodeURIComponent($("#reason").val() || "");

    if (docId) {
      window.location.href =
        "book.php?doctor_id=" + docId +
        "&patient_name=" + name +
        "&phone=" + phone +
        "&email=" + email +
        "&appointment_date=" + date +
        "&reason=" + reason;
    } else {
      window.location.href = "book.php";
    }

  });

  // Reload page when appointment date changes (important for slot locking)
  $("#appointment_date").on("change", function () {

    clearError("#appointment_date", "#err_date");

    const docId = $("#doctor_id").val();
    const date = encodeURIComponent($(this).val());

    const name = encodeURIComponent($("#patient_name").val() || "");
    const phone = encodeURIComponent($("#phone").val() || "");
    const email = encodeURIComponent($("#email").val() || "");
    const reason = encodeURIComponent($("#reason").val() || "");

    if (docId) {
      window.location.href =
        "book.php?doctor_id=" + docId +
        "&appointment_date=" + date +
        "&patient_name=" + name +
        "&phone=" + phone +
        "&email=" + email +
        "&reason=" + reason;
    }

  });

  // Submit validation
  $("#appointmentForm").submit(function (e) {

    let valid = true;

    $(".err").text("");
    $(".input").css("border-color", "rgba(255,255,255,.18)");

    const name = $("#patient_name").val().trim();
    const phone = $("#phone").val().trim();
    const email = $("#email").val().trim();
    const doctor = $("#doctor_id").val();
    const date = $("#appointment_date").val();
    const time = $("#appointment_time").val();
    const reason = $("#reason").val().trim();

    // Name validation
    if (name === "") {
      setError("#patient_name", "#err_patient", "Patient name is required.");
      valid = false;
    } else if (!/^[A-Za-z\s.]+$/.test(name)) {
      setError("#patient_name", "#err_patient", "Only letters and spaces allowed.");
      valid = false;
    }

    // Phone validation
    const phonePattern = /^07\d{8}$/;
    if (phone === "") {
      setError("#phone", "#err_phone", "Phone number is required.");
      valid = false;
    } else if (!phonePattern.test(phone)) {
      setError("#phone", "#err_phone", "Enter valid phone number (07XXXXXXXX).");
      valid = false;
    }

    // Email validation
    const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/;
    if (email === "") {
      setError("#email", "#err_email", "Email is required.");
      valid = false;
    } else if (!emailPattern.test(email)) {
      setError("#email", "#err_email", "Enter a valid email.");
      valid = false;
    }

    // Doctor validation
    if (!doctor) {
      setError("#doctor_id", "#err_doctor", "Please select a doctor.");
      valid = false;
    }

    // Date validation
    if (date === "") {
      setError("#appointment_date", "#err_date", "Select appointment date.");
      valid = false;
    } else if (date < today) {
      setError("#appointment_date", "#err_date", "Date cannot be in the past.");
      valid = false;
    }

    // Time validation
    if (!time) {
      setError("#appointment_time", "#err_time", "Select a valid time slot.");
      valid = false;
    }

    // Reason validation
    if (reason === "") {
      setError("#reason", "#err_reason", "Reason is required.");
      valid = false;
    } else if (reason.length < 5) {
      setError("#reason", "#err_reason", "Reason must be at least 5 characters.");
      valid = false;
    }

    if (!valid) e.preventDefault();

  });

});