# Returns Flutter User-App UI Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Add the return flow to the Flutter user app — a `ReturnsModel` + `ReturnsCubit` + a real returns screen (my-returns list + request), wired to the returns API.

**Architecture:** Follows the app's existing conventions: a Cubit doing raw `http` with `Bearer $token` (token from `LoginCubit`) against `Constants.apiBaseUrl`; methods RETURN a result the screen awaits (mirroring `OrderCubit.changeOrderStatus`); models via `fromJson`. Do NOT re-architect the app's single-state/no-repository debt — consistency wins.

**Tech Stack:** Flutter 3.38 (installed at `/c/flutter/bin`), flutter_bloc 8.x, http 1.x. Model tests run via `flutter test`. Verify with `flutter analyze` + `flutter test` + `flutter build apk --debug`.

## Global Constraints

- All work in `C:\xampp\htdocs\Narzin\Narzin-app\user\narzin` — run `flutter` commands from there.
- Reason value sent to the API is exactly one of `damaged, wrong_item, not_as_described, no_longer_needed, other` (labels display-only). (from spec)
- Cubit HTTP: `Constants.apiBaseUrl` + path; header `Authorization: 'Bearer $token'`; guard HTML error responses (`response.body.trimLeft().startsWith('<!DOCTYPE')`) before `json.decode`, like `OrderCubit`. (from spec)
- `requestReturn` RETURNS `null` on success or the backend `message` string on failure (screen awaits + shows snackbar), mirroring `OrderCubit.changeOrderStatus`. (from spec)
- No re-architecture; match `OrderCubit`'s `isLoading` + `emit` idiom so the screen's `BlocBuilder`/`context.read` stays consistent. (from spec)

---

### Task 1: ReturnsModel + parse test

**Files:**
- Create: `lib/model_layer/returns_model.dart`
- Test: `test/returns_model_test.dart`

**Interfaces:**
- Produces `ReturnsModel.fromJson(Map)` → `{ bool? status, List<ReturnItem> data }`; `ReturnItem { int? id; int? orderId; String? reason; String? status; String? refundAmount; String? requestedAt; String? orderNumber; }`. Tasks 2–3 consume it.

- [ ] **Step 1: Write the failing test**

Create `test/returns_model_test.dart` (mirrors `test/home_blocks_model_test.dart`):

```dart
import 'package:flutter_test/flutter_test.dart';
import 'package:narzin/model_layer/returns_model.dart';

void main() {
  test('parses returns with the eager-loaded order relation', () {
    final model = ReturnsModel.fromJson({
      'status': true,
      'data': [
        {
          'id': 5,
          'order_id': 42,
          'reason': 'damaged',
          'status': 'requested',
          'refund_amount': null,
          'requested_at': '2026-07-12T10:00:00Z',
          'order': {'order_number': 'ORD-042'},
        },
      ],
    });

    expect(model.status, true);
    expect(model.data.length, 1);
    expect(model.data[0].id, 5);
    expect(model.data[0].orderId, 42);
    expect(model.data[0].reason, 'damaged');
    expect(model.data[0].status, 'requested');
    expect(model.data[0].orderNumber, 'ORD-042');
  });

  test('tolerates a missing order relation', () {
    final model = ReturnsModel.fromJson({
      'status': true,
      'data': [
        {'id': 6, 'order_id': 7, 'reason': 'other', 'status': 'refunded', 'refund_amount': '50.00', 'requested_at': '2026-07-11'},
      ],
    });

    expect(model.data[0].orderNumber, isNull);
    expect(model.data[0].refundAmount, '50.00');
  });

  test('tolerates empty/absent data', () {
    final model = ReturnsModel.fromJson({'status': true});
    expect(model.data, isEmpty);
  });
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `flutter test test/returns_model_test.dart`
Expected: FAIL — `returns_model.dart` doesn't exist / URI doesn't resolve.

- [ ] **Step 3: Write the model**

Create `lib/model_layer/returns_model.dart`:

```dart
class ReturnsModel {
  final bool? status;
  final List<ReturnItem> data;

  ReturnsModel({this.status, required this.data});

  factory ReturnsModel.fromJson(Map<String, dynamic> json) {
    final rawList = json['data'];
    final list = (rawList is List)
        ? rawList.map((e) => ReturnItem.fromJson(e as Map<String, dynamic>)).toList()
        : <ReturnItem>[];
    return ReturnsModel(status: json['status'] as bool?, data: list);
  }
}

class ReturnItem {
  final int? id;
  final int? orderId;
  final String? reason;
  final String? status;
  final String? refundAmount;
  final String? requestedAt;
  final String? orderNumber;

  ReturnItem({
    this.id,
    this.orderId,
    this.reason,
    this.status,
    this.refundAmount,
    this.requestedAt,
    this.orderNumber,
  });

  factory ReturnItem.fromJson(Map<String, dynamic> json) {
    final order = json['order'];
    return ReturnItem(
      id: json['id'] is int ? json['id'] as int : int.tryParse('${json['id']}'),
      orderId: json['order_id'] is int ? json['order_id'] as int : int.tryParse('${json['order_id']}'),
      reason: json['reason']?.toString(),
      status: json['status']?.toString(),
      refundAmount: json['refund_amount']?.toString(),
      requestedAt: json['requested_at']?.toString(),
      orderNumber: (order is Map<String, dynamic>) ? order['order_number']?.toString() : null,
    );
  }
}
```

- [ ] **Step 4: Run test to verify it passes**

Run: `flutter test test/returns_model_test.dart`
Expected: PASS (3 tests).

- [ ] **Step 5: Analyze the new file**

Run: `flutter analyze lib/model_layer/returns_model.dart test/returns_model_test.dart`
Expected: No issues (or only pre-existing project-wide infos unrelated to these files).

- [ ] **Step 6: Commit**

```bash
git add lib/model_layer/returns_model.dart test/returns_model_test.dart
git commit -m "feat(flutter-returns): ReturnsModel + parse test"
```

---

### Task 2: ReturnsCubit + state + register

**Files:**
- Create: `lib/bussiness_logic/returns_cubits/returns_cubit.dart`
- Create: `lib/bussiness_logic/returns_cubits/returns_state.dart`
- Modify: `lib/main.dart` (register `ReturnsCubit` in `MultiBlocProvider`)

**Interfaces:**
- Consumes: `ReturnsModel` (Task 1), `Constants.apiBaseUrl`.
- Produces `ReturnsCubit` with `ReturnsModel? returnsModel`, `bool isLoading`, `Future fetchReturns({required String token})`, `Future<String?> requestReturn({required String token, required int orderId, required String reason, String? note})` (returns null on success, message on failure). Task 3 consumes it.

- [ ] **Step 1: Write the state**

Create `lib/bussiness_logic/returns_cubits/returns_state.dart`:

```dart
part of 'returns_cubit.dart';

@immutable
sealed class ReturnsState {}

final class ReturnsInitial extends ReturnsState {}
```

- [ ] **Step 2: Write the cubit**

Create `lib/bussiness_logic/returns_cubits/returns_cubit.dart` (mirrors `OrderCubit`'s http/isLoading idiom):

```dart
import 'dart:convert';
import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:http/http.dart' as http;
import '../../core/constants.dart';
import '../../model_layer/returns_model.dart';

part 'returns_state.dart';

class ReturnsCubit extends Cubit<ReturnsState> {
  ReturnsCubit() : super(ReturnsInitial());

  ReturnsModel? returnsModel;
  bool isLoading = false;

  bool _isHtml(String body) => body.trimLeft().startsWith('<!DOCTYPE') || body.trimLeft().startsWith('<html');

  Future<void> fetchReturns({required String token}) async {
    isLoading = true;
    emit(ReturnsInitial());
    try {
      final response = await http.get(
        Uri.parse('${Constants.apiBaseUrl}returns'),
        headers: {'Content-Type': 'application/json', 'Authorization': 'Bearer $token'},
      );
      if (!_isHtml(response.body) && response.statusCode >= 200 && response.statusCode < 300) {
        returnsModel = ReturnsModel.fromJson(json.decode(response.body) as Map<String, dynamic>);
      }
    } catch (_) {
      // swallow — leave returnsModel as-is; the screen shows an empty/error state
    } finally {
      isLoading = false;
      emit(ReturnsInitial());
    }
  }

  /// Returns null on success, or the backend error message on failure.
  Future<String?> requestReturn({
    required String token,
    required int orderId,
    required String reason,
    String? note,
  }) async {
    isLoading = true;
    emit(ReturnsInitial());
    try {
      final response = await http.post(
        Uri.parse('${Constants.apiBaseUrl}orders/$orderId/returns'),
        headers: {'Content-Type': 'application/json', 'Authorization': 'Bearer $token'},
        body: json.encode({'reason': reason, 'note': note}),
      );
      isLoading = false;
      emit(ReturnsInitial());

      if (response.statusCode >= 200 && response.statusCode < 300) {
        return null;
      }
      if (_isHtml(response.body)) return 'Something went wrong. Please try again.';
      final decoded = json.decode(response.body);
      return (decoded is Map && decoded['message'] != null)
          ? decoded['message'].toString()
          : 'Failed to request return.';
    } catch (_) {
      isLoading = false;
      emit(ReturnsInitial());
      return 'Network error. Please try again.';
    }
  }
}
```

- [ ] **Step 3: Register in main.dart**

In `lib/main.dart`, add the import near the other cubit imports:

```dart
import 'bussiness_logic/returns_cubits/returns_cubit.dart';
```

Then add a provider inside the `MultiBlocProvider` `providers: [...]` list (next to `OrderCubit`):

```dart
            BlocProvider(create: (context) => ReturnsCubit()),
```

(Match the exact import path style and provider formatting used by the surrounding providers — some use `import 'bussiness_logic/...';` relative to `lib/`.)

- [ ] **Step 4: Analyze**

Run: `flutter analyze lib/bussiness_logic/returns_cubits/ lib/main.dart`
Expected: No new issues on these files.

- [ ] **Step 5: Confirm the model test still passes (nothing broke)**

Run: `flutter test test/returns_model_test.dart`
Expected: PASS.

- [ ] **Step 6: Commit**

```bash
git add lib/bussiness_logic/returns_cubits/returns_cubit.dart lib/bussiness_logic/returns_cubits/returns_state.dart lib/main.dart
git commit -m "feat(flutter-returns): ReturnsCubit (fetch + request) registered in main"
```

---

### Task 3: Rework returns_screen.dart (my-returns list + request)

**Files:**
- Modify (rework): `lib/presentation_layer/main_app_user/profile_screens/my_orders_screen/returns_screen.dart`

**Interfaces:**
- Consumes: `ReturnsCubit` (Task 2), `OrderCubit.myOrdersModel` (existing order history), `LoginCubit` (token).

- [ ] **Step 1: Rework the screen**

Rework `returns_screen.dart` so it:

- On init (`StatefulWidget` or an init dispatch): reads `token` from
  `BlocProvider.of<LoginCubit>(context).loginModel?.data?.token ?? ''` and calls
  `context.read<ReturnsCubit>().fetchReturns(token: token)`. (`OrderCubit.getMyOrder`
  is already dispatched by the profile menu before navigation, so `myOrdersModel`
  is populated for the request list.)
- Wraps content in `BlocBuilder<ReturnsCubit, ReturnsState>` and reads
  `context.read<ReturnsCubit>().returnsModel` + `.isLoading`.
- **My Returns section:** if `isLoading` and no data → a loading indicator; else
  map `returnsModel?.data` → each row: `orderNumber ?? 'Order #$orderId'`, a
  readable reason label (a local `Map<String,String>` of the 5 reasons), a status
  chip colored by status (requested/approved/rejected/refunded), and
  `requestedAt`. Empty-state text when the list is empty.
- **Request a Return section:** the existing eligible-orders list (from
  `context.read<OrderCubit>().myOrdersModel`, filtered to `payment_status` in
  `['completed','processing']`). Tapping an order opens a dialog/bottom sheet with
  a reason picker (5 readable options → enum value) + an optional note
  `TextField`. On confirm: `final err = await context.read<ReturnsCubit>()
  .requestReturn(token: token, orderId: order.id, reason: selectedReason, note: note);`
  then: if `err == null` → success `SnackBar` + `fetchReturns(token)` refresh;
  else → `SnackBar(content: Text(err))` (the backend message).
- Keep the existing app bar / styling idiom. All display via `Text(...)` (no HTML).
  Reason value sent = the exact enum string.

> This screen currently reads `OrderCubit`'s order list and filters by
> `Helpers.orderStatus`. Preserve the app's visual style; you may keep helper
> widgets. The key change is: add the real "My Returns" list from `ReturnsCubit`
> and make the order rows trigger a `requestReturn` via the reason dialog. If the
> existing screen structure makes a clean rework hard, a full rewrite of this one
> file is acceptable — but do not touch other screens or `OrderCubit`.

- [ ] **Step 2: Analyze**

Run: `flutter analyze lib/presentation_layer/main_app_user/profile_screens/my_orders_screen/returns_screen.dart`
Expected: No new issues on this file. (Pre-existing project-wide analyzer infos are acceptable; do not introduce new errors/warnings on this file.)

- [ ] **Step 3: Build to confirm it compiles**

Run: `flutter build apk --debug`
Expected: BUILD SUCCESSFUL. (If the full APK build is too slow/heavy in this environment, run `flutter analyze` over the whole `lib/` instead and confirm no NEW errors were introduced by this branch — note which you did in the report.)

- [ ] **Step 4: Commit**

```bash
git add lib/presentation_layer/main_app_user/profile_screens/my_orders_screen/returns_screen.dart
git commit -m "feat(flutter-returns): real returns screen — my-returns list + request flow"
```

---

## Definition of done

- `flutter test test/returns_model_test.dart` green; `flutter analyze` introduces no new errors on the new/changed files; the app compiles (`flutter build apk --debug`, or full-lib analyze if build is impractical — noted in the report).
- The Returns screen (already reachable from the profile menu) shows the user's real returns (status chips, empty-state) and lets them request a return on an eligible order (reason + optional note) with success/error snackbars carrying the backend message.
- Follows the app's cubit/http/token conventions; no re-architecture; only the returns model/cubit/state/screen + the main.dart provider line are touched.
