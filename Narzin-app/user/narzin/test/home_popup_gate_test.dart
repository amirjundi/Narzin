import 'package:flutter_test/flutter_test.dart';
import 'package:narzin/core/home_popup_gate.dart';
import 'package:narzin/model_layer/home_blocks_model.dart';
import 'package:shared_preferences/shared_preferences.dart';

HomeBlock popup(int id, String mode, {int days = 0}) => HomeBlock(
      id: id,
      type: 'popup',
      content: {
        'frequency': {'mode': mode, 'days': days}
      },
    );

void main() {
  setUp(() {
    SharedPreferences.setMockInitialValues({});
    HomePopupGate.resetForTests();
  });

  test('session popup shows once per app run', () async {
    final prefs = await SharedPreferences.getInstance();
    final block = popup(5, 'once_per_session');
    expect(HomePopupGate.shouldShow(block, prefs), true);
    await HomePopupGate.markShown(block, prefs);
    expect(HomePopupGate.shouldShow(block, prefs), false);
  });

  test('days popup respects the N-day window', () async {
    final prefs = await SharedPreferences.getInstance();
    final block = popup(6, 'once_per_days', days: 7);
    final now = DateTime.utc(2026, 7, 3);
    expect(HomePopupGate.shouldShow(block, prefs, now: now), true);
    await HomePopupGate.markShown(block, prefs, now: now);
    HomePopupGate.resetForTests(); // new app run, but prefs persist
    expect(
        HomePopupGate.shouldShow(block, prefs,
            now: now.add(const Duration(days: 6))),
        false);
    expect(
        HomePopupGate.shouldShow(block, prefs,
            now: now.add(const Duration(days: 8))),
        true);
  });

  test('blocks are independent per id', () async {
    final prefs = await SharedPreferences.getInstance();
    await HomePopupGate.markShown(popup(5, 'once_per_session'), prefs);
    expect(HomePopupGate.shouldShow(popup(9, 'once_per_session'), prefs), true);
  });
}
