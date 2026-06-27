/// Validates the name field
String? validateName(String? name) {
  if (name == null || name.trim().isEmpty) {
    return 'Name cannot be empty.';
  } else if (name.length < 3) {
    return 'Name must be at least 3 characters long.';
  } else if (!RegExp(r'^[a-zA-Z\s]+$').hasMatch(name)) {
    return 'Name can only contain letters and spaces.';
  }
  return null; // No error
}

/// Validates the email field
String? validateEmail(String? email) {
  if (email == null || email.trim().isEmpty) {
    return 'Email cannot be empty.';
  } else if (!RegExp(
      r'^[a-zA-Z0-9._%-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,4}$') // Basic email validation regex
      .hasMatch(email)) {
    return 'Enter a valid email address.';
  }
  return null; // No error
}


/// Validates the password field (minimum 8 characters)
String? validatePassword(String? password) {
  if (password == null || password.trim().isEmpty) {
    return 'Password cannot be empty.';
  } else if (password.length < 8) {
    return 'Password must be at least 8 characters long.';
  }
  return null; // No error
}

String? validatePhoneNumber(String? value) {
  if (value == null || value.isEmpty) {
    return 'Please enter a phone number';
  }

  // Egyptian phone number regex (without +20, starts with 0 only)
  final egyptianRegExp = RegExp(r'^0(10|11|12|15)\d{8}$');

  // German phone number regex
  final germanRegExp = RegExp(r'^(?:\+49|0)(1[5-7]|[2-9][0-9])\d{6,10}$');

  if (!egyptianRegExp.hasMatch(value) && !germanRegExp.hasMatch(value)) {
    return 'Invalid phone number. Please enter a valid Egyptian or German phone number.';
  }

  return null; // Valid phone number
}

String? validateEmptyField(String? value) {
  if (value == null || value.isEmpty) {
    return 'Please Don\'t leave this field empty.';
  }

  return null; // Valid phone number
}