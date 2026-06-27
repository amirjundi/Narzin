part of 'connectivity_cubit.dart';

@immutable
sealed class ConnectivityState {}

final class ConnectivityInitial extends ConnectivityState {}

class ConnectivityListen extends ConnectivityState {}

class ConnectivityFailed extends ConnectivityState {}
