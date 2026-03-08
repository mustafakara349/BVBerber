import 'package:flutter_test/flutter_test.dart';
import 'package:mobile_android/main.dart';

void main() {
  testWidgets('BVBarber app smoke test', (WidgetTester tester) async {
    await tester.pumpWidget(const BVBarberApp());

    // Uygulama başlatıldığında login ekranının gösterildiğini doğrula
    expect(find.text('BVBarber'), findsOneWidget);
  });
}
