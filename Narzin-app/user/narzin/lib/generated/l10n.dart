// GENERATED CODE - DO NOT MODIFY BY HAND
import 'package:flutter/material.dart';
import 'package:intl/intl.dart';
import 'intl/messages_all.dart';

// **************************************************************************
// Generator: Flutter Intl IDE plugin
// Made by Localizely
// **************************************************************************

// ignore_for_file: non_constant_identifier_names, lines_longer_than_80_chars
// ignore_for_file: join_return_with_assignment, prefer_final_in_for_each
// ignore_for_file: avoid_redundant_argument_values, avoid_escaping_inner_quotes

class S {
  S();

  static S? _current;

  static S get current {
    assert(
      _current != null,
      'No instance of S was loaded. Try to initialize the S delegate before accessing S.current.',
    );
    return _current!;
  }

  static const AppLocalizationDelegate delegate = AppLocalizationDelegate();

  static Future<S> load(Locale locale) {
    final name =
        (locale.countryCode?.isEmpty ?? false)
            ? locale.languageCode
            : locale.toString();
    final localeName = Intl.canonicalizedLocale(name);
    return initializeMessages(localeName).then((_) {
      Intl.defaultLocale = localeName;
      final instance = S();
      S._current = instance;

      return instance;
    });
  }

  static S of(BuildContext context) {
    final instance = S.maybeOf(context);
    assert(
      instance != null,
      'No instance of S present in the widget tree. Did you add S.delegate in localizationsDelegates?',
    );
    return instance!;
  }

  static S? maybeOf(BuildContext context) {
    return Localizations.of<S>(context, S);
  }

  /// `Your orders are ready, wherever you are`
  String get ready_wherever_you_are {
    return Intl.message(
      'Your orders are ready, wherever you are',
      name: 'ready_wherever_you_are',
      desc: '',
      args: [],
    );
  }

  /// `Narzin: Your style, your way`
  String get narzin_your_style_your_way {
    return Intl.message(
      'Narzin: Your style, your way',
      name: 'narzin_your_style_your_way',
      desc: '',
      args: [],
    );
  }

  /// `Discover fashion that expresses your personality. With Narzin, redefine your wardrobe with modern elegance.`
  String get discover_fashion_expressing_you {
    return Intl.message(
      'Discover fashion that expresses your personality. With Narzin, redefine your wardrobe with modern elegance.',
      name: 'discover_fashion_expressing_you',
      desc: '',
      args: [],
    );
  }

  /// `Elevate your closet`
  String get elevate_your_closet {
    return Intl.message(
      'Elevate your closet',
      name: 'elevate_your_closet',
      desc: '',
      args: [],
    );
  }

  /// `Step into the world of trendy fashion. Narzin brings the latest trends and classic designs to your hands.`
  String get step_into_trendy_fashion {
    return Intl.message(
      'Step into the world of trendy fashion. Narzin brings the latest trends and classic designs to your hands.',
      name: 'step_into_trendy_fashion',
      desc: '',
      args: [],
    );
  }

  /// `More than just fashion`
  String get more_than_fashion {
    return Intl.message(
      'More than just fashion',
      name: 'more_than_fashion',
      desc: '',
      args: [],
    );
  }

  /// `Shop all you need and more! From clothes to accessories and other products, Narzin offers a comprehensive shopping experience for all your needs.`
  String get shop_all_you_need {
    return Intl.message(
      'Shop all you need and more! From clothes to accessories and other products, Narzin offers a comprehensive shopping experience for all your needs.',
      name: 'shop_all_you_need',
      desc: '',
      args: [],
    );
  }

  /// `Skip`
  String get skip {
    return Intl.message('Skip', name: 'skip', desc: '', args: []);
  }

  /// `Sign In`
  String get sign_in {
    return Intl.message('Sign In', name: 'sign_in', desc: '', args: []);
  }

  /// `Create Account`
  String get create_account {
    return Intl.message(
      'Create Account',
      name: 'create_account',
      desc: '',
      args: [],
    );
  }

  /// `Or with`
  String get or_with {
    return Intl.message('Or with', name: 'or_with', desc: '', args: []);
  }

  /// `Welcome back with`
  String get welcome_back_with {
    return Intl.message(
      'Welcome back with',
      name: 'welcome_back_with',
      desc: '',
      args: [],
    );
  }

  /// `Register a new account`
  String get register_new_account {
    return Intl.message(
      'Register a new account',
      name: 'register_new_account',
      desc: '',
      args: [],
    );
  }

  /// `Don't have an account?`
  String get dont_have_account {
    return Intl.message(
      'Don\'t have an account?',
      name: 'dont_have_account',
      desc: '',
      args: [],
    );
  }

  /// `Email`
  String get email {
    return Intl.message('Email', name: 'email', desc: '', args: []);
  }

  /// `Please enter your email`
  String get enter_your_email {
    return Intl.message(
      'Please enter your email',
      name: 'enter_your_email',
      desc: '',
      args: [],
    );
  }

  /// `Password`
  String get password {
    return Intl.message('Password', name: 'password', desc: '', args: []);
  }

  /// `Please enter your password`
  String get enter_your_password {
    return Intl.message(
      'Please enter your password',
      name: 'enter_your_password',
      desc: '',
      args: [],
    );
  }

  /// `Forgot password?`
  String get forgot_password {
    return Intl.message(
      'Forgot password?',
      name: 'forgot_password',
      desc: '',
      args: [],
    );
  }

  /// `Remember me`
  String get remember_me {
    return Intl.message('Remember me', name: 'remember_me', desc: '', args: []);
  }

  /// `Start your journey with`
  String get start_your_journey_with {
    return Intl.message(
      'Start your journey with',
      name: 'start_your_journey_with',
      desc: '',
      args: [],
    );
  }

  /// `Create a new user account`
  String get create_new_user_account {
    return Intl.message(
      'Create a new user account',
      name: 'create_new_user_account',
      desc: '',
      args: [],
    );
  }

  /// `Already have an account?`
  String get already_have_account {
    return Intl.message(
      'Already have an account?',
      name: 'already_have_account',
      desc: '',
      args: [],
    );
  }

  /// `Full Name`
  String get full_name {
    return Intl.message('Full Name', name: 'full_name', desc: '', args: []);
  }

  /// `Please enter your full name`
  String get enter_your_full_name {
    return Intl.message(
      'Please enter your full name',
      name: 'enter_your_full_name',
      desc: '',
      args: [],
    );
  }

  /// `Use 8 or more characters with a mix of letters, numbers, and symbols.`
  String get password_guidelines {
    return Intl.message(
      'Use 8 or more characters with a mix of letters, numbers, and symbols.',
      name: 'password_guidelines',
      desc: '',
      args: [],
    );
  }

  /// `Register`
  String get register {
    return Intl.message('Register', name: 'register', desc: '', args: []);
  }

  /// `Email Confirmation`
  String get email_confirmation {
    return Intl.message(
      'Email Confirmation',
      name: 'email_confirmation',
      desc: '',
      args: [],
    );
  }

  /// `We will send you a one-time link to confirm your email.`
  String get send_email_confirmation_link {
    return Intl.message(
      'We will send you a one-time link to confirm your email.',
      name: 'send_email_confirmation_link',
      desc: '',
      args: [],
    );
  }

  /// `Didn't receive the email?`
  String get didnt_receive_email {
    return Intl.message(
      'Didn\'t receive the email?',
      name: 'didnt_receive_email',
      desc: '',
      args: [],
    );
  }

  /// `Resend`
  String get resend {
    return Intl.message('Resend', name: 'resend', desc: '', args: []);
  }

  /// `Confirm`
  String get confirm {
    return Intl.message('Confirm', name: 'confirm', desc: '', args: []);
  }

  /// `Password changed successfully`
  String get password_changed_successfully {
    return Intl.message(
      'Password changed successfully',
      name: 'password_changed_successfully',
      desc: '',
      args: [],
    );
  }

  /// `Your password has been successfully changed`
  String get password_changed_success_message {
    return Intl.message(
      'Your password has been successfully changed',
      name: 'password_changed_success_message',
      desc: '',
      args: [],
    );
  }

  /// `Create a new password`
  String get create_new_password {
    return Intl.message(
      'Create a new password',
      name: 'create_new_password',
      desc: '',
      args: [],
    );
  }

  /// `Back to your journey`
  String get back_to_your_journey {
    return Intl.message(
      'Back to your journey',
      name: 'back_to_your_journey',
      desc: '',
      args: [],
    );
  }

  /// `New Password`
  String get new_password {
    return Intl.message(
      'New Password',
      name: 'new_password',
      desc: '',
      args: [],
    );
  }

  /// `Confirm Password`
  String get confirm_password {
    return Intl.message(
      'Confirm Password',
      name: 'confirm_password',
      desc: '',
      args: [],
    );
  }

  /// `Please re-enter your password`
  String get reenter_password {
    return Intl.message(
      'Please re-enter your password',
      name: 'reenter_password',
      desc: '',
      args: [],
    );
  }

  /// `Reset Password`
  String get reset_password {
    return Intl.message(
      'Reset Password',
      name: 'reset_password',
      desc: '',
      args: [],
    );
  }

  /// `Back to Sign In`
  String get back_to_sign_in {
    return Intl.message(
      'Back to Sign In',
      name: 'back_to_sign_in',
      desc: '',
      args: [],
    );
  }

  /// `Success`
  String get success {
    return Intl.message('Success', name: 'success', desc: '', args: []);
  }

  /// `Please check your email to create a new password`
  String get check_email_reset_password {
    return Intl.message(
      'Please check your email to create a new password',
      name: 'check_email_reset_password',
      desc: '',
      args: [],
    );
  }

  /// `Didn't get the code?`
  String get didnt_get_code {
    return Intl.message(
      'Didn\'t get the code?',
      name: 'didnt_get_code',
      desc: '',
      args: [],
    );
  }

  /// `Resend Code`
  String get resend_code {
    return Intl.message('Resend Code', name: 'resend_code', desc: '', args: []);
  }

  /// `Forgot Password`
  String get forgot_password_title {
    return Intl.message(
      'Forgot Password',
      name: 'forgot_password_title',
      desc: '',
      args: [],
    );
  }

  /// `To return to your account, enter your email`
  String get forgot_password_description {
    return Intl.message(
      'To return to your account, enter your email',
      name: 'forgot_password_description',
      desc: '',
      args: [],
    );
  }

  /// `Remember the password?`
  String get remember_password {
    return Intl.message(
      'Remember the password?',
      name: 'remember_password',
      desc: '',
      args: [],
    );
  }

  /// `Send Code`
  String get send_code {
    return Intl.message('Send Code', name: 'send_code', desc: '', args: []);
  }

  /// `Home`
  String get home {
    return Intl.message('Home', name: 'home', desc: '', args: []);
  }

  /// `Categories`
  String get categories {
    return Intl.message('Categories', name: 'categories', desc: '', args: []);
  }

  /// `Cart`
  String get cart {
    return Intl.message('Cart', name: 'cart', desc: '', args: []);
  }

  /// `My Account`
  String get my_account {
    return Intl.message('My Account', name: 'my_account', desc: '', args: []);
  }

  /// `Orders`
  String get orders {
    return Intl.message('Orders', name: 'orders', desc: '', args: []);
  }

  /// `Returns`
  String get returns {
    return Intl.message('Returns', name: 'returns', desc: '', args: []);
  }

  /// `Cards`
  String get cards {
    return Intl.message('Cards', name: 'cards', desc: '', args: []);
  }

  /// `Wallet`
  String get wallet {
    return Intl.message('Wallet', name: 'wallet', desc: '', args: []);
  }

  /// `Favorites`
  String get favorites {
    return Intl.message('Favorites', name: 'favorites', desc: '', args: []);
  }

  /// `Settings`
  String get settings {
    return Intl.message('Settings', name: 'settings', desc: '', args: []);
  }

  /// `Contact Us`
  String get contact_us {
    return Intl.message('Contact Us', name: 'contact_us', desc: '', args: []);
  }

  /// `About Us`
  String get about_us {
    return Intl.message('About Us', name: 'about_us', desc: '', args: []);
  }

  /// `Register as Merchant`
  String get register_as_merchant {
    return Intl.message(
      'Register as Merchant',
      name: 'register_as_merchant',
      desc: '',
      args: [],
    );
  }

  /// `Log Out`
  String get logout {
    return Intl.message('Log Out', name: 'logout', desc: '', args: []);
  }

  /// `Account`
  String get account {
    return Intl.message('Account', name: 'account', desc: '', args: []);
  }

  /// `Save`
  String get save {
    return Intl.message('Save', name: 'save', desc: '', args: []);
  }

  /// `Cancel`
  String get cancel {
    return Intl.message('Cancel', name: 'cancel', desc: '', args: []);
  }

  /// `Language`
  String get language {
    return Intl.message('Language', name: 'language', desc: '', args: []);
  }

  /// `English`
  String get english {
    return Intl.message('English', name: 'english', desc: '', args: []);
  }

  /// `Arabic`
  String get arabic {
    return Intl.message('Arabic', name: 'arabic', desc: '', args: []);
  }

  /// `Notification`
  String get notification {
    return Intl.message(
      'Notification',
      name: 'notification',
      desc: '',
      args: [],
    );
  }

  /// `Delete Account`
  String get delete_account {
    return Intl.message(
      'Delete Account',
      name: 'delete_account',
      desc: '',
      args: [],
    );
  }

  /// `Deleting your account will remove all your information from our database. This action is irreversible.`
  String get delete_account_message {
    return Intl.message(
      'Deleting your account will remove all your information from our database. This action is irreversible.',
      name: 'delete_account_message',
      desc: '',
      args: [],
    );
  }

  /// `Delete Account`
  String get delete_account_button {
    return Intl.message(
      'Delete Account',
      name: 'delete_account_button',
      desc: '',
      args: [],
    );
  }

  /// `Change Password`
  String get change_password {
    return Intl.message(
      'Change Password',
      name: 'change_password',
      desc: '',
      args: [],
    );
  }

  /// `Old Password`
  String get old_password {
    return Intl.message(
      'Old Password',
      name: 'old_password',
      desc: '',
      args: [],
    );
  }

  /// `Save Changes`
  String get save_changes {
    return Intl.message(
      'Save Changes',
      name: 'save_changes',
      desc: '',
      args: [],
    );
  }

  /// `Cancel Changes`
  String get cancel_changes {
    return Intl.message(
      'Cancel Changes',
      name: 'cancel_changes',
      desc: '',
      args: [],
    );
  }

  /// `Who We Are`
  String get who_we_are {
    return Intl.message('Who We Are', name: 'who_we_are', desc: '', args: []);
  }

  /// `Why Choose Us?`
  String get why_choose_us {
    return Intl.message(
      'Why Choose Us?',
      name: 'why_choose_us',
      desc: '',
      args: [],
    );
  }

  /// `A comprehensive experience touching your spirit: We understand the spiritual value of supplications that strengthen your relationship with Allah and bring you peace and comfort. That’s why the app is designed to be your daily companion on your spiritual journey, with personalized supplications and reminder notifications to ensure you never miss a dhikr.`
  String get spiritual_experience {
    return Intl.message(
      'A comprehensive experience touching your spirit: We understand the spiritual value of supplications that strengthen your relationship with Allah and bring you peace and comfort. That’s why the app is designed to be your daily companion on your spiritual journey, with personalized supplications and reminder notifications to ensure you never miss a dhikr.',
      name: 'spiritual_experience',
      desc: '',
      args: [],
    );
  }

  /// `Continuous motivation with a fun challenge: Maintaining your dhikr is easier and more encouraging with badges and challenge systems. Whether completing a weekly challenge or achieving a streak of consecutive days, the app offers a sense of accomplishment and ongoing motivation.`
  String get continuous_motivation {
    return Intl.message(
      'Continuous motivation with a fun challenge: Maintaining your dhikr is easier and more encouraging with badges and challenge systems. Whether completing a weekly challenge or achieving a streak of consecutive days, the app offers a sense of accomplishment and ongoing motivation.',
      name: 'continuous_motivation',
      desc: '',
      args: [],
    );
  }

  /// `A simple and intuitive interface with a touch of creativity: You won’t need much time to get accustomed to the app, as the user interface is designed to be comfortable and appealing, making it easy to find the supplications and tools you need.`
  String get simple_interface {
    return Intl.message(
      'A simple and intuitive interface with a touch of creativity: You won’t need much time to get accustomed to the app, as the user interface is designed to be comfortable and appealing, making it easy to find the supplications and tools you need.',
      name: 'simple_interface',
      desc: '',
      args: [],
    );
  }

  /// `A unique community that inspires you: The app provides an opportunity to join a community of users through leaderboards, where you can interact with friends, see their progress, and share common goals, making the experience more engaging and fun. Friendly competition motivates you to persevere in your dhikr.`
  String get inspiring_community {
    return Intl.message(
      'A unique community that inspires you: The app provides an opportunity to join a community of users through leaderboards, where you can interact with friends, see their progress, and share common goals, making the experience more engaging and fun. Friendly competition motivates you to persevere in your dhikr.',
      name: 'inspiring_community',
      desc: '',
      args: [],
    );
  }

  /// `Detailed statistics to track your progress and set goals: Nothing motivates you more than seeing your accomplishments grow day by day. With precise statistics showing the number of completed dhikrs, streak days, and challenges achieved, the app provides an overview of your journey and inspires you to achieve more.`
  String get detailed_statistics {
    return Intl.message(
      'Detailed statistics to track your progress and set goals: Nothing motivates you more than seeing your accomplishments grow day by day. With precise statistics showing the number of completed dhikrs, streak days, and challenges achieved, the app provides an overview of your journey and inspires you to achieve more.',
      name: 'detailed_statistics',
      desc: '',
      args: [],
    );
  }

  /// `This app is not just a tool for reading dhikr; it's your daily spiritual companion that offers everything you need for spiritual connection, challenge, and motivation, with a modern touch that suits your world.`
  String get not_just_app {
    return Intl.message(
      'This app is not just a tool for reading dhikr; it\'s your daily spiritual companion that offers everything you need for spiritual connection, challenge, and motivation, with a modern touch that suits your world.',
      name: 'not_just_app',
      desc: '',
      args: [],
    );
  }

  /// `Contact Us`
  String get contact_us_button {
    return Intl.message(
      'Contact Us',
      name: 'contact_us_button',
      desc: '',
      args: [],
    );
  }

  /// `Merchant Account Request`
  String get merchant_account_request {
    return Intl.message(
      'Merchant Account Request',
      name: 'merchant_account_request',
      desc: '',
      args: [],
    );
  }

  /// `Please select your specialized category`
  String get choose_category {
    return Intl.message(
      'Please select your specialized category',
      name: 'choose_category',
      desc: '',
      args: [],
    );
  }

  /// `Category`
  String get category {
    return Intl.message('Category', name: 'category', desc: '', args: []);
  }

  /// `Phone Number`
  String get phone {
    return Intl.message('Phone Number', name: 'phone', desc: '', args: []);
  }

  /// `Address`
  String get address {
    return Intl.message('Address', name: 'address', desc: '', args: []);
  }

  /// `Please enter your phone number`
  String get phone_hint {
    return Intl.message(
      'Please enter your phone number',
      name: 'phone_hint',
      desc: '',
      args: [],
    );
  }

  /// `Please enter your store address`
  String get address_hint {
    return Intl.message(
      'Please enter your store address',
      name: 'address_hint',
      desc: '',
      args: [],
    );
  }

  /// `Store Name in Arabic`
  String get store_name_ar {
    return Intl.message(
      'Store Name in Arabic',
      name: 'store_name_ar',
      desc: '',
      args: [],
    );
  }

  /// `Store Name in German`
  String get store_name_en {
    return Intl.message(
      'Store Name in German',
      name: 'store_name_en',
      desc: '',
      args: [],
    );
  }

  /// `Description`
  String get description {
    return Intl.message('Description', name: 'description', desc: '', args: []);
  }

  /// `Please write a clear and accurate description of your store`
  String get write_store_description {
    return Intl.message(
      'Please write a clear and accurate description of your store',
      name: 'write_store_description',
      desc: '',
      args: [],
    );
  }

  /// `Send`
  String get send {
    return Intl.message('Send', name: 'send', desc: '', args: []);
  }

  /// `Congratulations, it has been`
  String get congratulations {
    return Intl.message(
      'Congratulations, it has been',
      name: 'congratulations',
      desc: '',
      args: [],
    );
  }

  /// `Your request has been successfully sent`
  String get request_successfully_sent {
    return Intl.message(
      'Your request has been successfully sent',
      name: 'request_successfully_sent',
      desc: '',
      args: [],
    );
  }

  /// `Thank you for your efforts and time. Your request will be reviewed, and you will be contacted as soon as possible.`
  String get thank_you_message {
    return Intl.message(
      'Thank you for your efforts and time. Your request will be reviewed, and you will be contacted as soon as possible.',
      name: 'thank_you_message',
      desc: '',
      args: [],
    );
  }

  /// `Go to Home`
  String get go_to_home {
    return Intl.message('Go to Home', name: 'go_to_home', desc: '', args: []);
  }

  /// `Edit`
  String get edit {
    return Intl.message('Edit', name: 'edit', desc: '', args: []);
  }

  /// `Welcome to`
  String get welcome_message {
    return Intl.message(
      'Welcome to',
      name: 'welcome_message',
      desc: '',
      args: [],
    );
  }

  /// `Narzin`
  String get app_name {
    return Intl.message('Narzin', name: 'app_name', desc: '', args: []);
  }

  /// `What are you looking for?`
  String get search_placeholder {
    return Intl.message(
      'What are you looking for?',
      name: 'search_placeholder',
      desc: '',
      args: [],
    );
  }

  /// `Statistics`
  String get statistics {
    return Intl.message('Statistics', name: 'statistics', desc: '', args: []);
  }

  /// `Daily`
  String get daily {
    return Intl.message('Daily', name: 'daily', desc: '', args: []);
  }

  /// `Weekly`
  String get weekly {
    return Intl.message('Weekly', name: 'weekly', desc: '', args: []);
  }

  /// `Monthly`
  String get monthly {
    return Intl.message('Monthly', name: 'monthly', desc: '', args: []);
  }

  /// `Total Users`
  String get total_users {
    return Intl.message('Total Users', name: 'total_users', desc: '', args: []);
  }

  /// `Total Orders`
  String get total_orders {
    return Intl.message(
      'Total Orders',
      name: 'total_orders',
      desc: '',
      args: [],
    );
  }

  /// `Total Sales`
  String get total_sales {
    return Intl.message('Total Sales', name: 'total_sales', desc: '', args: []);
  }

  /// `Total Pending`
  String get total_pending {
    return Intl.message(
      'Total Pending',
      name: 'total_pending',
      desc: '',
      args: [],
    );
  }

  /// `New Orders`
  String get new_orders {
    return Intl.message('New Orders', name: 'new_orders', desc: '', args: []);
  }

  /// `Cash on Delivery`
  String get cash_on_delivery {
    return Intl.message(
      'Cash on Delivery',
      name: 'cash_on_delivery',
      desc: '',
      args: [],
    );
  }

  /// `Order Number`
  String get order_number {
    return Intl.message(
      'Order Number',
      name: 'order_number',
      desc: '',
      args: [],
    );
  }

  /// `New`
  String get status_new {
    return Intl.message('New', name: 'status_new', desc: '', args: []);
  }

  /// `Active`
  String get status_active {
    return Intl.message('Active', name: 'status_active', desc: '', args: []);
  }

  /// `Completed`
  String get status_completed {
    return Intl.message(
      'Completed',
      name: 'status_completed',
      desc: '',
      args: [],
    );
  }

  /// `Cancelled`
  String get status_cancelled {
    return Intl.message(
      'Cancelled',
      name: 'status_cancelled',
      desc: '',
      args: [],
    );
  }

  /// `Sort by:`
  String get sort_by {
    return Intl.message('Sort by:', name: 'sort_by', desc: '', args: []);
  }

  /// `Nearest`
  String get sort_nearest {
    return Intl.message('Nearest', name: 'sort_nearest', desc: '', args: []);
  }

  /// `Order`
  String get order_title {
    return Intl.message('Order', name: 'order_title', desc: '', args: []);
  }

  /// `Products`
  String get products {
    return Intl.message('Products', name: 'products', desc: '', args: []);
  }

  /// `Product Name`
  String get product_name {
    return Intl.message(
      'Product Name',
      name: 'product_name',
      desc: '',
      args: [],
    );
  }

  /// `Color`
  String get color {
    return Intl.message('Color', name: 'color', desc: '', args: []);
  }

  /// `Size`
  String get size {
    return Intl.message('Size', name: 'size', desc: '', args: []);
  }

  /// `Total`
  String get total {
    return Intl.message('Total', name: 'total', desc: '', args: []);
  }

  /// `Paid with Wallet`
  String get paid_with_wallet {
    return Intl.message(
      'Paid with Wallet',
      name: 'paid_with_wallet',
      desc: '',
      args: [],
    );
  }

  /// `Customer Name`
  String get customer_name {
    return Intl.message(
      'Customer Name',
      name: 'customer_name',
      desc: '',
      args: [],
    );
  }

  /// `Status`
  String get status {
    return Intl.message('Status', name: 'status', desc: '', args: []);
  }

  /// `Cancelled`
  String get status_cancelled_text {
    return Intl.message(
      'Cancelled',
      name: 'status_cancelled_text',
      desc: '',
      args: [],
    );
  }

  /// `Delivery Time`
  String get delivery_time {
    return Intl.message(
      'Delivery Time',
      name: 'delivery_time',
      desc: '',
      args: [],
    );
  }

  /// `Order not processed`
  String get order_not_processed {
    return Intl.message(
      'Order not processed',
      name: 'order_not_processed',
      desc: '',
      args: [],
    );
  }

  /// `Active`
  String get status_active_text {
    return Intl.message(
      'Active',
      name: 'status_active_text',
      desc: '',
      args: [],
    );
  }

  /// `Pending`
  String get status_pending {
    return Intl.message('Pending', name: 'status_pending', desc: '', args: []);
  }

  /// `All`
  String get status_all {
    return Intl.message('All', name: 'status_all', desc: '', args: []);
  }

  /// `Add Product`
  String get add_product {
    return Intl.message('Add Product', name: 'add_product', desc: '', args: []);
  }

  /// `Basic Data`
  String get basic_data {
    return Intl.message('Basic Data', name: 'basic_data', desc: '', args: []);
  }

  /// `Details`
  String get details {
    return Intl.message('Details', name: 'details', desc: '', args: []);
  }

  /// `Name in Arabic`
  String get name_ar {
    return Intl.message('Name in Arabic', name: 'name_ar', desc: '', args: []);
  }

  /// `Please enter a clear product name in Arabic`
  String get name_ar_placeholder {
    return Intl.message(
      'Please enter a clear product name in Arabic',
      name: 'name_ar_placeholder',
      desc: '',
      args: [],
    );
  }

  /// `Please enter a clear product name in German`
  String get name_de_placeholder {
    return Intl.message(
      'Please enter a clear product name in German',
      name: 'name_de_placeholder',
      desc: '',
      args: [],
    );
  }

  /// `Name in German`
  String get name_de {
    return Intl.message('Name in German', name: 'name_de', desc: '', args: []);
  }

  /// `Please provide accurate and clear details of the product in Arabic`
  String get product_details_ar {
    return Intl.message(
      'Please provide accurate and clear details of the product in Arabic',
      name: 'product_details_ar',
      desc: '',
      args: [],
    );
  }

  /// `Please provide accurate and clear details of the product in German`
  String get product_details_de {
    return Intl.message(
      'Please provide accurate and clear details of the product in German',
      name: 'product_details_de',
      desc: '',
      args: [],
    );
  }

  /// `Please upload product images/videos`
  String get product_media {
    return Intl.message(
      'Please upload product images/videos',
      name: 'product_media',
      desc: '',
      args: [],
    );
  }

  /// `Category or type name with the ability to add a new type`
  String get category_name {
    return Intl.message(
      'Category or type name with the ability to add a new type',
      name: 'category_name',
      desc: '',
      args: [],
    );
  }

  /// `Type`
  String get type {
    return Intl.message('Type', name: 'type', desc: '', args: []);
  }

  /// `Details in Arabic`
  String get details_ar {
    return Intl.message(
      'Details in Arabic',
      name: 'details_ar',
      desc: '',
      args: [],
    );
  }

  /// `Details in German`
  String get details_de {
    return Intl.message(
      'Details in German',
      name: 'details_de',
      desc: '',
      args: [],
    );
  }

  /// `Product Images/Videos`
  String get product_images_videos {
    return Intl.message(
      'Product Images/Videos',
      name: 'product_images_videos',
      desc: '',
      args: [],
    );
  }

  /// `Next`
  String get next {
    return Intl.message('Next', name: 'next', desc: '', args: []);
  }

  /// `Storage Capacity`
  String get storage_capacity {
    return Intl.message(
      'Storage Capacity',
      name: 'storage_capacity',
      desc: '',
      args: [],
    );
  }

  /// `RAM Capacity`
  String get ram_capacity {
    return Intl.message(
      'RAM Capacity',
      name: 'ram_capacity',
      desc: '',
      args: [],
    );
  }

  /// `Colors`
  String get colors {
    return Intl.message('Colors', name: 'colors', desc: '', args: []);
  }

  /// `Quantity`
  String get quantity {
    return Intl.message('Quantity', name: 'quantity', desc: '', args: []);
  }

  /// `Selling Price`
  String get selling_price {
    return Intl.message(
      'Selling Price',
      name: 'selling_price',
      desc: '',
      args: [],
    );
  }

  /// `Add`
  String get add {
    return Intl.message('Add', name: 'add', desc: '', args: []);
  }

  /// `Discount (if available)`
  String get discount_optional {
    return Intl.message(
      'Discount (if available)',
      name: 'discount_optional',
      desc: '',
      args: [],
    );
  }

  /// `Discount Code`
  String get discount_code {
    return Intl.message(
      'Discount Code',
      name: 'discount_code',
      desc: '',
      args: [],
    );
  }

  /// `Multi-select for available colors with the ability to add colors`
  String get multiple_colors_info {
    return Intl.message(
      'Multi-select for available colors with the ability to add colors',
      name: 'multiple_colors_info',
      desc: '',
      args: [],
    );
  }

  /// `Discount Expiry Date`
  String get discount_expiry_date {
    return Intl.message(
      'Discount Expiry Date',
      name: 'discount_expiry_date',
      desc: '',
      args: [],
    );
  }

  /// `Back`
  String get back {
    return Intl.message('Back', name: 'back', desc: '', args: []);
  }

  /// `Add New Attribute`
  String get add_new_attribute {
    return Intl.message(
      'Add New Attribute',
      name: 'add_new_attribute',
      desc: '',
      args: [],
    );
  }

  /// `Attribute Title`
  String get attribute_title {
    return Intl.message(
      'Attribute Title',
      name: 'attribute_title',
      desc: '',
      args: [],
    );
  }

  /// `Attribute Type`
  String get attribute_type {
    return Intl.message(
      'Attribute Type',
      name: 'attribute_type',
      desc: '',
      args: [],
    );
  }

  /// `Please enter the new attribute title`
  String get attribute_title_placeholder {
    return Intl.message(
      'Please enter the new attribute title',
      name: 'attribute_title_placeholder',
      desc: '',
      args: [],
    );
  }

  /// `Select the type of the new attribute, such as Name, Number, Color...`
  String get attribute_type_placeholder {
    return Intl.message(
      'Select the type of the new attribute, such as Name, Number, Color...',
      name: 'attribute_type_placeholder',
      desc: '',
      args: [],
    );
  }

  /// `Store Image`
  String get store_image {
    return Intl.message('Store Image', name: 'store_image', desc: '', args: []);
  }

  /// `Store ID`
  String get store_id {
    return Intl.message('Store ID', name: 'store_id', desc: '', args: []);
  }

  /// `Please upload the store ID`
  String get store_id_placeholder {
    return Intl.message(
      'Please upload the store ID',
      name: 'store_id_placeholder',
      desc: '',
      args: [],
    );
  }

  /// `Please upload the store image`
  String get store_image_placeholder {
    return Intl.message(
      'Please upload the store image',
      name: 'store_image_placeholder',
      desc: '',
      args: [],
    );
  }

  /// `Be different`
  String get be_different {
    return Intl.message(
      'Be different',
      name: 'be_different',
      desc: '',
      args: [],
    );
  }

  /// `Most popular products`
  String get most_popular_products {
    return Intl.message(
      'Most popular products',
      name: 'most_popular_products',
      desc: '',
      args: [],
    );
  }

  /// `Discover fashion that expresses your personality. With Narzin, redefine your wardrobe.`
  String get discover_fashion {
    return Intl.message(
      'Discover fashion that expresses your personality. With Narzin, redefine your wardrobe.',
      name: 'discover_fashion',
      desc: '',
      args: [],
    );
  }

  /// `Most requested`
  String get most_requested {
    return Intl.message(
      'Most requested',
      name: 'most_requested',
      desc: '',
      args: [],
    );
  }

  /// `Most popular`
  String get most_popular {
    return Intl.message(
      'Most popular',
      name: 'most_popular',
      desc: '',
      args: [],
    );
  }

  /// `More`
  String get more {
    return Intl.message('More', name: 'more', desc: '', args: []);
  }

  /// `Set delivery location`
  String get set_delivery_location {
    return Intl.message(
      'Set delivery location',
      name: 'set_delivery_location',
      desc: '',
      args: [],
    );
  }

  /// `Home`
  String get home_page {
    return Intl.message('Home', name: 'home_page', desc: '', args: []);
  }

  /// `Add to cart`
  String get add_to_cart {
    return Intl.message('Add to cart', name: 'add_to_cart', desc: '', args: []);
  }

  /// `Suggested`
  String get suggested {
    return Intl.message('Suggested', name: 'suggested', desc: '', args: []);
  }

  /// `Reviews`
  String get reviews {
    return Intl.message('Reviews', name: 'reviews', desc: '', args: []);
  }

  /// `Positive`
  String get positive {
    return Intl.message('Positive', name: 'positive', desc: '', args: []);
  }

  /// `Critical`
  String get critical {
    return Intl.message('Critical', name: 'critical', desc: '', args: []);
  }

  /// `Notifications`
  String get notifications {
    return Intl.message(
      'Notifications',
      name: 'notifications',
      desc: '',
      args: [],
    );
  }

  /// `You have placed an order`
  String get order_placed {
    return Intl.message(
      'You have placed an order',
      name: 'order_placed',
      desc: '',
      args: [],
    );
  }

  /// `And paid`
  String get payment_done {
    return Intl.message('And paid', name: 'payment_done', desc: '', args: []);
  }

  /// `Your order will arrive soon.`
  String get order_arrival {
    return Intl.message(
      'Your order will arrive soon.',
      name: 'order_arrival',
      desc: '',
      args: [],
    );
  }

  /// `Filter`
  String get filter {
    return Intl.message('Filter', name: 'filter', desc: '', args: []);
  }

  /// `Price`
  String get price {
    return Intl.message('Price', name: 'price', desc: '', args: []);
  }

  /// `Search`
  String get search {
    return Intl.message('Search', name: 'search', desc: '', args: []);
  }

  /// `Recent searches`
  String get recent_searches {
    return Intl.message(
      'Recent searches',
      name: 'recent_searches',
      desc: '',
      args: [],
    );
  }

  /// `Top search keywords`
  String get top_search_keywords {
    return Intl.message(
      'Top search keywords',
      name: 'top_search_keywords',
      desc: '',
      args: [],
    );
  }

  /// `No results found`
  String get no_results_found {
    return Intl.message(
      'No results found',
      name: 'no_results_found',
      desc: '',
      args: [],
    );
  }

  /// `Sorry, the keyword you entered was not found.\n Please check again or try using a different keyword.`
  String get no_results_message {
    return Intl.message(
      'Sorry, the keyword you entered was not found.\n Please check again or try using a different keyword.',
      name: 'no_results_message',
      desc: '',
      args: [],
    );
  }

  /// `Report an error`
  String get report_error {
    return Intl.message(
      'Report an error',
      name: 'report_error',
      desc: '',
      args: [],
    );
  }

  /// `Problem`
  String get problem {
    return Intl.message('Problem', name: 'problem', desc: '', args: []);
  }

  /// `Additional feedback`
  String get additional_feedback {
    return Intl.message(
      'Additional feedback',
      name: 'additional_feedback',
      desc: '',
      args: [],
    );
  }

  /// `Select your device issue`
  String get select_device_issue {
    return Intl.message(
      'Select your device issue',
      name: 'select_device_issue',
      desc: '',
      args: [],
    );
  }

  /// `Describe your problem in more detail`
  String get describe_problem {
    return Intl.message(
      'Describe your problem in more detail',
      name: 'describe_problem',
      desc: '',
      args: [],
    );
  }

  /// `Subcategories`
  String get subcategories {
    return Intl.message(
      'Subcategories',
      name: 'subcategories',
      desc: '',
      args: [],
    );
  }

  /// `Current shipping`
  String get current_shipping {
    return Intl.message(
      'Current shipping',
      name: 'current_shipping',
      desc: '',
      args: [],
    );
  }

  /// `Previous orders`
  String get previous_orders {
    return Intl.message(
      'Previous orders',
      name: 'previous_orders',
      desc: '',
      args: [],
    );
  }

  /// `Sold by`
  String get sold_by {
    return Intl.message('Sold by', name: 'sold_by', desc: '', args: []);
  }

  /// `Apply`
  String get apply {
    return Intl.message('Apply', name: 'apply', desc: '', args: []);
  }

  /// `Order summary`
  String get order_summary {
    return Intl.message(
      'Order summary',
      name: 'order_summary',
      desc: '',
      args: [],
    );
  }

  /// `Subtotal`
  String get subtotal {
    return Intl.message('Subtotal', name: 'subtotal', desc: '', args: []);
  }

  /// `Tax`
  String get tax {
    return Intl.message('Tax', name: 'tax', desc: '', args: []);
  }

  /// `Estimated delivery date`
  String get estimated_delivery_date {
    return Intl.message(
      'Estimated delivery date',
      name: 'estimated_delivery_date',
      desc: '',
      args: [],
    );
  }

  /// `Checkout`
  String get checkout {
    return Intl.message('Checkout', name: 'checkout', desc: '', args: []);
  }

  /// `The cart looks empty`
  String get empty_cart {
    return Intl.message(
      'The cart looks empty',
      name: 'empty_cart',
      desc: '',
      args: [],
    );
  }

  /// `The home page should have some interesting things.`
  String get home_page_message {
    return Intl.message(
      'The home page should have some interesting things.',
      name: 'home_page_message',
      desc: '',
      args: [],
    );
  }

  /// `Delivery To`
  String get delivery_to {
    return Intl.message('Delivery To', name: 'delivery_to', desc: '', args: []);
  }

  /// `Reset`
  String get reset {
    return Intl.message('Reset', name: 'reset', desc: '', args: []);
  }

  /// `Initialize`
  String get initialize {
    return Intl.message('Initialize', name: 'initialize', desc: '', args: []);
  }

  /// `Sort`
  String get sort {
    return Intl.message('Sort', name: 'sort', desc: '', args: []);
  }

  /// `Available pieces`
  String get available_stock {
    return Intl.message(
      'Available pieces',
      name: 'available_stock',
      desc: '',
      args: [],
    );
  }

  /// `Size`
  String get sizes {
    return Intl.message('Size', name: 'sizes', desc: '', args: []);
  }

  /// `Insert The Quantity`
  String get quantity_placeholder {
    return Intl.message(
      'Insert The Quantity',
      name: 'quantity_placeholder',
      desc: '',
      args: [],
    );
  }

  /// `Product Variant`
  String get product_variant {
    return Intl.message(
      'Product Variant',
      name: 'product_variant',
      desc: '',
      args: [],
    );
  }

  /// `Initializing Your Order`
  String get order_initialization {
    return Intl.message(
      'Initializing Your Order',
      name: 'order_initialization',
      desc: '',
      args: [],
    );
  }

  /// `Add a Review`
  String get add_review {
    return Intl.message('Add a Review', name: 'add_review', desc: '', args: []);
  }

  /// `Add a Comment`
  String get add_comment {
    return Intl.message(
      'Add a Comment',
      name: 'add_comment',
      desc: '',
      args: [],
    );
  }

  /// `Rating`
  String get rating {
    return Intl.message('Rating', name: 'rating', desc: '', args: []);
  }

  /// `Clear Cart`
  String get clear_cart {
    return Intl.message('Clear Cart', name: 'clear_cart', desc: '', args: []);
  }

  /// `Add a new address`
  String get add_address {
    return Intl.message(
      'Add a new address',
      name: 'add_address',
      desc: '',
      args: [],
    );
  }

  /// `Location`
  String get location {
    return Intl.message('Location', name: 'location', desc: '', args: []);
  }

  /// `Please enter your location on the map`
  String get enter_location {
    return Intl.message(
      'Please enter your location on the map',
      name: 'enter_location',
      desc: '',
      args: [],
    );
  }

  /// `City`
  String get city {
    return Intl.message('City', name: 'city', desc: '', args: []);
  }

  /// `Please select the city you reside in`
  String get enter_city_name {
    return Intl.message(
      'Please select the city you reside in',
      name: 'enter_city_name',
      desc: '',
      args: [],
    );
  }

  /// `Street`
  String get street {
    return Intl.message('Street', name: 'street', desc: '', args: []);
  }

  /// `Please enter the name of your street`
  String get enter_street_name {
    return Intl.message(
      'Please enter the name of your street',
      name: 'enter_street_name',
      desc: '',
      args: [],
    );
  }

  /// `Apartment Number`
  String get apartment_number {
    return Intl.message(
      'Apartment Number',
      name: 'apartment_number',
      desc: '',
      args: [],
    );
  }

  /// `Building Number`
  String get building_number {
    return Intl.message(
      'Building Number',
      name: 'building_number',
      desc: '',
      args: [],
    );
  }

  /// `Enter your apartment number`
  String get enter_apartment_number {
    return Intl.message(
      'Enter your apartment number',
      name: 'enter_apartment_number',
      desc: '',
      args: [],
    );
  }

  /// `Enter your building number.`
  String get enter_building_number {
    return Intl.message(
      'Enter your building number.',
      name: 'enter_building_number',
      desc: '',
      args: [],
    );
  }

  /// `Complete Order`
  String get complete_order {
    return Intl.message(
      'Complete Order',
      name: 'complete_order',
      desc: '',
      args: [],
    );
  }

  /// `Your order has been successfully confirmed`
  String get order_confirmed {
    return Intl.message(
      'Your order has been successfully confirmed',
      name: 'order_confirmed',
      desc: '',
      args: [],
    );
  }

  /// `Shipping Type`
  String get shipping_type {
    return Intl.message(
      'Shipping Type',
      name: 'shipping_type',
      desc: '',
      args: [],
    );
  }

  /// `Fast Shipping`
  String get fast_shipping {
    return Intl.message(
      'Fast Shipping',
      name: 'fast_shipping',
      desc: '',
      args: [],
    );
  }

  /// `We will deliver within`
  String get shipping_details {
    return Intl.message(
      'We will deliver within',
      name: 'shipping_details',
      desc: '',
      args: [],
    );
  }

  /// `days`
  String get days {
    return Intl.message('days', name: 'days', desc: '', args: []);
  }

  /// `Standard Shipping`
  String get standard_shipping {
    return Intl.message(
      'Standard Shipping',
      name: 'standard_shipping',
      desc: '',
      args: [],
    );
  }

  /// `We will deliver within 3-4 days`
  String get standard_shipping_details {
    return Intl.message(
      'We will deliver within 3-4 days',
      name: 'standard_shipping_details',
      desc: '',
      args: [],
    );
  }

  /// `Payment with`
  String get payment_with {
    return Intl.message(
      'Payment with',
      name: 'payment_with',
      desc: '',
      args: [],
    );
  }

  /// `Country`
  String get country {
    return Intl.message('Country', name: 'country', desc: '', args: []);
  }

  /// `Please select the country you reside in`
  String get enter_country_name {
    return Intl.message(
      'Please select the country you reside in',
      name: 'enter_country_name',
      desc: '',
      args: [],
    );
  }

  /// `Please enter the full address`
  String get full_address_hint {
    return Intl.message(
      'Please enter the full address',
      name: 'full_address_hint',
      desc: '',
      args: [],
    );
  }

  /// `Postal Code`
  String get postal_code {
    return Intl.message('Postal Code', name: 'postal_code', desc: '', args: []);
  }

  /// `Please enter the postal code`
  String get postal_code_hint {
    return Intl.message(
      'Please enter the postal code',
      name: 'postal_code_hint',
      desc: '',
      args: [],
    );
  }

  /// `You have no saved Addresses...`
  String get no_saved_addresses {
    return Intl.message(
      'You have no saved Addresses...',
      name: 'no_saved_addresses',
      desc: '',
      args: [],
    );
  }

  /// `No extra fees`
  String get no_extra_fees {
    return Intl.message(
      'No extra fees',
      name: 'no_extra_fees',
      desc: '',
      args: [],
    );
  }

  /// `Extra fees applied`
  String get extra_fees_applied {
    return Intl.message(
      'Extra fees applied',
      name: 'extra_fees_applied',
      desc: '',
      args: [],
    );
  }

  /// `Advance Order`
  String get advance_order {
    return Intl.message(
      'Advance Order',
      name: 'advance_order',
      desc: '',
      args: [],
    );
  }

  /// `Your wishlist is empty..`
  String get your_wishlist_is_empty {
    return Intl.message(
      'Your wishlist is empty..',
      name: 'your_wishlist_is_empty',
      desc: '',
      args: [],
    );
  }

  /// `No categories available yet...`
  String get no_categories_available {
    return Intl.message(
      'No categories available yet...',
      name: 'no_categories_available',
      desc: '',
      args: [],
    );
  }

  /// `No products available yet...`
  String get no_products_available {
    return Intl.message(
      'No products available yet...',
      name: 'no_products_available',
      desc: '',
      args: [],
    );
  }

  /// `Add Coupon`
  String get coupon {
    return Intl.message('Add Coupon', name: 'coupon', desc: '', args: []);
  }

  /// `Coupon Applied Successfully.`
  String get coupon_applied {
    return Intl.message(
      'Coupon Applied Successfully.',
      name: 'coupon_applied',
      desc: '',
      args: [],
    );
  }

  /// `Coupon isn't applied!!!`
  String get coupon_failed {
    return Intl.message(
      'Coupon isn\'t applied!!!',
      name: 'coupon_failed',
      desc: '',
      args: [],
    );
  }

  /// `Your Balance`
  String get your_balance {
    return Intl.message(
      'Your Balance',
      name: 'your_balance',
      desc: '',
      args: [],
    );
  }

  /// `Latest Transactions`
  String get latest_transactions {
    return Intl.message(
      'Latest Transactions',
      name: 'latest_transactions',
      desc: '',
      args: [],
    );
  }

  /// `Added`
  String get added {
    return Intl.message('Added', name: 'added', desc: '', args: []);
  }

  /// `to your balance`
  String get to_your_balance {
    return Intl.message(
      'to your balance',
      name: 'to_your_balance',
      desc: '',
      args: [],
    );
  }

  /// `through one of our delivery partners`
  String get through_our_delivery_partner {
    return Intl.message(
      'through one of our delivery partners',
      name: 'through_our_delivery_partner',
      desc: '',
      args: [],
    );
  }

  /// `Order Confirmed`
  String get order_confirmed2 {
    return Intl.message(
      'Order Confirmed',
      name: 'order_confirmed2',
      desc: '',
      args: [],
    );
  }

  /// `Pending`
  String get pending {
    return Intl.message('Pending', name: 'pending', desc: '', args: []);
  }

  /// `Out for Delivery`
  String get out_for_delivery {
    return Intl.message(
      'Out for Delivery',
      name: 'out_for_delivery',
      desc: '',
      args: [],
    );
  }

  /// `Delivered`
  String get delivered {
    return Intl.message('Delivered', name: 'delivered', desc: '', args: []);
  }

  /// `Cancelled`
  String get cancelled {
    return Intl.message('Cancelled', name: 'cancelled', desc: '', args: []);
  }

  /// `Return`
  String get returned {
    return Intl.message('Return', name: 'returned', desc: '', args: []);
  }

  /// `Cancelled Operations`
  String get cancelled_operations {
    return Intl.message(
      'Cancelled Operations',
      name: 'cancelled_operations',
      desc: '',
      args: [],
    );
  }

  /// `No cards here yet, hurry up and add your first card`
  String get no_cards_message {
    return Intl.message(
      'No cards here yet, hurry up and add your first card',
      name: 'no_cards_message',
      desc: '',
      args: [],
    );
  }

  /// `German`
  String get language_german {
    return Intl.message('German', name: 'language_german', desc: '', args: []);
  }

  /// `Guest Login`
  String get guest_login {
    return Intl.message('Guest Login', name: 'guest_login', desc: '', args: []);
  }

  /// `InAccessible function you should Login first or Sign up...`
  String get inaccessible_view {
    return Intl.message(
      'InAccessible function you should Login first or Sign up...',
      name: 'inaccessible_view',
      desc: '',
      args: [],
    );
  }

  /// `Withdrawn`
  String get withdrawn {
    return Intl.message('Withdrawn', name: 'withdrawn', desc: '', args: []);
  }

  /// `From your balance`
  String get from_your_balance {
    return Intl.message(
      'From your balance',
      name: 'from_your_balance',
      desc: '',
      args: [],
    );
  }

  /// `Address Name`
  String get title {
    return Intl.message('Address Name', name: 'title', desc: '', args: []);
  }

  /// `e.g. Home, Work, Grandma's House`
  String get title_hint {
    return Intl.message("e.g. Home, Work, Grandma's House", name: 'title_hint', desc: '', args: []);
  }

  /// `Out of Stock`
  String get out_of_stock {
    return Intl.message(
      'Out of Stock',
      name: 'out_of_stock',
      desc: '',
      args: [],
    );
  }

  /// `The tax is calculated in detail at the time of booking.`
  String get TaxDetail {
    return Intl.message(
      'The tax is calculated in detail at the time of booking.',
      name: 'TaxDetail',
      desc: '',
      args: [],
    );
  }

  /// `Default`
  String get deflt {
    return Intl.message('Default', name: 'deflt', desc: '', args: []);
  }
}

class AppLocalizationDelegate extends LocalizationsDelegate<S> {
  const AppLocalizationDelegate();

  List<Locale> get supportedLocales {
    return const <Locale>[
      Locale.fromSubtags(languageCode: 'en'),
      Locale.fromSubtags(languageCode: 'ar'),
      Locale.fromSubtags(languageCode: 'de'),
    ];
  }

  @override
  bool isSupported(Locale locale) => _isSupported(locale);
  @override
  Future<S> load(Locale locale) => S.load(locale);
  @override
  bool shouldReload(AppLocalizationDelegate old) => false;

  bool _isSupported(Locale locale) {
    for (var supportedLocale in supportedLocales) {
      if (supportedLocale.languageCode == locale.languageCode) {
        return true;
      }
    }
    return false;
  }
}
