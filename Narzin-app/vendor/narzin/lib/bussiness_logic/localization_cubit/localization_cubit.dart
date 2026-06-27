import 'package:bloc/bloc.dart';
import 'package:meta/meta.dart';
import 'package:shared_preferences/shared_preferences.dart';
part 'localization_state.dart';

class LocalizationCubit extends Cubit<LocalizationState> {
  LocalizationCubit() : super(LocalizationInitial());
  String locale = 'ar';
  int selectedIndex = 0;
  List<String> availableLanguages = ['de','ar'];
  late SharedPreferences _prefs;

  changeLocale() async {
    selectedIndex = (selectedIndex + 1) % availableLanguages.length;
    locale = availableLanguages[selectedIndex];
    await setLocale();
    emit(LocaleChange());
  }

  setLang(String loc) async {
    locale = loc;
    await setLocale();
    emit(LocaleChange());
  }

  getLocale() async {
    try {
      _prefs = await SharedPreferences.getInstance();
      var res = _prefs.getString('locale');
      if (res != null) {
        locale = res;
        emit(Memorize());
      } else {
        locale = 'ar';
        emit(Memorize());
      }
    } catch (e) {
      locale = 'ar';
      emit(Memorize());
      return null;
    }
  }

  setLocale() async {
    _prefs = await SharedPreferences.getInstance();
    _prefs.setString('locale', locale);
  }
}
