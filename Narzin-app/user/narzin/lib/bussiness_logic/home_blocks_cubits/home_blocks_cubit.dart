import 'dart:convert';

import 'package:bloc/bloc.dart';
import 'package:flutter/material.dart';
import 'package:http/http.dart' as http;

import '../../core/constants.dart';
import '../../model_layer/home_blocks_model.dart';

part 'home_blocks_state.dart';

class HomeBlocksCubit extends Cubit<HomeBlocksState> {
  HomeBlocksCubit() : super(HomeBlocksInitial());

  List<HomeBlock> blocks = [];

  Future<void> getHomeBlocks({required String locale}) async {
    final apiUrl =
        '${Constants.apiBaseUrl}home?platform=app&locale=$locale';
    emit(HomeBlocksLoading());
    try {
      final response = await http.get(
        Uri.parse(apiUrl),
        headers: {'Accept': 'application/json'},
      );
      if (response.statusCode == 200) {
        final model = HomeBlocksModel.fromJson(
            json.decode(response.body) as Map<String, dynamic>);
        if (model.status == true) {
          blocks = model.blocks;
          emit(HomeBlocksLoaded());
          return;
        }
      }
      emit(HomeBlocksError());
    } catch (e) {
      debugPrint('home blocks fetch failed: $e');
      emit(HomeBlocksError());
    }
  }
}
