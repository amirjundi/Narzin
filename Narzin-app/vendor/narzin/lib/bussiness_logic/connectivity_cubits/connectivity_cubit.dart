import 'package:bloc/bloc.dart';
import 'package:meta/meta.dart';
import 'package:connectivity_plus/connectivity_plus.dart';
import 'package:logger/logger.dart';
part 'connectivity_state.dart';

class ConnectivityCubit extends Cubit<ConnectivityState> {
  ConnectivityCubit() : super(ConnectivityInitial());

  late String connected;

  ///method to check the connectivity of the internet connection and return it as a stream
  connectivityListener(){
    var logger = Logger();
    var subscription = Connectivity().onConnectivityChanged.listen((ConnectivityResult result) {
      logger.t(result.name);
      connected = result.name;
      if (connected == 'wifi' || connected == 'mobile') {
        emit(ConnectivityListen());
      }else if(connected == 'none'){
        emit(ConnectivityFailed());
      }

    });
  }


}
