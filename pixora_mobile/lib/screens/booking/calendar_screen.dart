// lib/screens/booking/calendar_screen.dart
import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:provider/provider.dart';
import 'package:table_calendar/table_calendar.dart';
import '../../config/theme.dart';
import '../../config/routes.dart';
import '../../providers/calendar_provider.dart';
import '../../widgets/slot_chip.dart';
import '../../widgets/gradient_button.dart';

class CalendarScreen extends StatefulWidget {
  const CalendarScreen({super.key});
  @override
  State<CalendarScreen> createState() => _CalendarScreenState();
}

class _CalendarScreenState extends State<CalendarScreen> {
  @override
  void initState() { super.initState(); Provider.of<CalendarProvider>(context, listen: false).fetchCalendarData(); }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: Text('Pilih Jadwal', style: GoogleFonts.poppins(fontWeight: FontWeight.bold))),
      body: Consumer<CalendarProvider>(builder: (ctx, cal, _) {
        if (cal.isLoading) return const Center(child: CircularProgressIndicator(color: PixoraTheme.primaryRose));
        final selectedKey = '${cal.selectedDate.year}-${cal.selectedDate.month.toString().padLeft(2, '0')}-${cal.selectedDate.day.toString().padLeft(2, '0')}';
        final dayData = cal.calendarData[selectedKey];

        return SingleChildScrollView(child: Column(children: [
          Container(margin: const EdgeInsets.all(16), decoration: BoxDecoration(color: Colors.white, borderRadius: BorderRadius.circular(20), boxShadow: PixoraTheme.softShadow),
            child: TableCalendar(
              firstDay: DateTime.now().subtract(const Duration(days: 1)),
              lastDay: DateTime.now().add(const Duration(days: 365)),
              focusedDay: cal.selectedDate,
              selectedDayPredicate: (day) => isSameDay(day, cal.selectedDate),
              onDaySelected: (selected, focused) {
                cal.selectDate(selected);
                if (selected.month != cal.currentMonth || selected.year != cal.currentYear) {
                  cal.fetchCalendarData(year: selected.year, month: selected.month);
                }
              },
              onPageChanged: (focused) => cal.fetchCalendarData(year: focused.year, month: focused.month),
              calendarStyle: CalendarStyle(
                todayDecoration: BoxDecoration(color: PixoraTheme.primaryRose.withValues(alpha: 0.3), shape: BoxShape.circle),
                selectedDecoration: const BoxDecoration(gradient: PixoraTheme.primaryGradient, shape: BoxShape.circle),
                outsideDaysVisible: false,
                weekendTextStyle: const TextStyle(color: PixoraTheme.error),
              ),
              headerStyle: HeaderStyle(
                formatButtonVisible: false, titleCentered: true,
                titleTextStyle: GoogleFonts.poppins(fontWeight: FontWeight.w600, fontSize: 16, color: PixoraTheme.dark),
                leftChevronIcon: const Icon(Icons.chevron_left, color: PixoraTheme.primaryRose),
                rightChevronIcon: const Icon(Icons.chevron_right, color: PixoraTheme.primaryRose),
              ),
              calendarBuilders: CalendarBuilders(markerBuilder: (ctx, date, events) {
                final key = '${date.year}-${date.month.toString().padLeft(2, '0')}-${date.day.toString().padLeft(2, '0')}';
                final d = cal.calendarData[key];
                if (d == null) return null;
                Color dotColor;
                if (d.isPast) { dotColor = PixoraTheme.gray; }
                else if (d.totalAvailableSlots == 3) { dotColor = PixoraTheme.success; }
                else if (d.totalAvailableSlots > 0) { dotColor = PixoraTheme.warning; }
                else { dotColor = PixoraTheme.error; }
                return Positioned(bottom: 1, child: Container(width: 6, height: 6, decoration: BoxDecoration(color: dotColor, shape: BoxShape.circle)));
              }),
            )),
          // Legend
          Padding(padding: const EdgeInsets.symmetric(horizontal: 20), child: Row(mainAxisAlignment: MainAxisAlignment.center, children: [
            _legend(PixoraTheme.success, 'Tersedia'), const SizedBox(width: 16),
            _legend(PixoraTheme.warning, 'Sebagian'), const SizedBox(width: 16),
            _legend(PixoraTheme.error, 'Penuh'),
          ])),
          const SizedBox(height: 20),

          // Slot selection
          if (dayData != null) ...[
            Padding(padding: const EdgeInsets.symmetric(horizontal: 20), child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
              Text('Pilih Slot Waktu', style: GoogleFonts.poppins(fontSize: 18, fontWeight: FontWeight.bold, color: PixoraTheme.dark)),
              const SizedBox(height: 12),
              ...dayData.slots.entries.map((e) => Padding(padding: const EdgeInsets.only(bottom: 8),
                child: SlotChip(slotKey: e.key, slot: e.value, isSelected: cal.selectedSlot == e.key,
                  onTap: () => cal.selectSlot(e.key)))),
              const SizedBox(height: 20),
              if (cal.selectedSlot != null) GradientButton(text: 'Lanjut ke Booking', icon: Icons.arrow_forward,
                onPressed: () => Navigator.pushNamed(context, AppRoutes.bookingForm)),
              const SizedBox(height: 20),
            ])),
          ] else Padding(padding: const EdgeInsets.all(20), child: Text('Pilih tanggal untuk melihat slot', style: TextStyle(color: PixoraTheme.gray))),
        ]));
      }),
    );
  }

  Widget _legend(Color color, String label) {
    return Row(children: [Container(width: 10, height: 10, decoration: BoxDecoration(color: color, shape: BoxShape.circle)), const SizedBox(width: 4),
      Text(label, style: const TextStyle(fontSize: 12, color: PixoraTheme.gray))]);
  }
}
