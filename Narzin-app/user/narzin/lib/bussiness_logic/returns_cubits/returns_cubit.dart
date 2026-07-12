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

      if (_isHtml(response.body)) return 'Something went wrong. Please try again.';
      if (response.statusCode >= 200 && response.statusCode < 300) {
        return null;
      }
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
