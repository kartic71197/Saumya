<section class="pt-24 pb-16 lg:pt-32 lg:pb-24 bg-[hsl(210,20%,98%)] overflow-hidden">
    <div class="max-w-7xl mx-auto px-6">
        <div class="grid lg:grid-cols-2 gap-16 items-center">

            <!-- LEFT CONTENT -->
<div class="flex flex-col items-center text-center space-y-6 lg:items-start lg:text-left">
    <!-- Heading -->
    <h1 class="text-5xl lg:text-6xl font-bold text-foreground leading-tight">
        Run Your Business<br>
        <span class="gradient-text">Smarter</span> with Saumya
    </h1>

    <!-- Paragraph -->
    <p class="text-lg text-muted-foreground max-w-lg">
        The all-in-one platform for salons, wellness centers, and medspas.
        Manage bookings, clients, staff, and revenue effortlessly.
    </p>

    <!-- Button -->
    <a href="#"
       class="px-12 py-3 rounded-xl border-2
              border-[hsl(262,83%,58%)]
              text-[hsl(262,83%,58%)]
              font-semibold
              hover:bg-[hsl(262,83%,58%,0.08)]
              transition-colors">
        Get a Demo
    </a>
</div>

            <!-- RIGHT DASHBOARD WRAPPER -->
            <div class="relative lg:pl-8">

                <!-- MAIN DASHBOARD CARD -->
                <div class="relative bg-white rounded-3xl
                            shadow-[0_40px_90px_rgba(0,0,0,0.08)]
                            border border-[hsl(210,20%,92%)]
                            overflow-hidden z-10">

                    <!-- HEADER -->
                    <div class="bg-[hsl(210, 19%, 94%)] px-6 py-4
            border-b border-[hsl(210,20%,92%)]
            flex items-center justify-between">

    <!-- Mac dots -->
    <div class="flex gap-2">
        <span class="w-3 h-3 rounded-full bg-red-400"></span>
        <span class="w-3 h-3 rounded-full bg-yellow-400"></span>
        <span class="w-3 h-3 rounded-full bg-green-400"></span>
    </div>

    <!-- Address Bar (SEPARATE SECTION) -->
    <div class="px-4 py-1.5 rounded-full
                bg-gray-100
                border border-[hsl(210,20%,92%)]
                shadow-[0_2px_6px_rgba(0,0,0,0.05)]
                text-sm text-muted-foreground">
        dashboard.saumya.com
    </div>

    <!-- Spacer for balance -->
    <div class="w-12"></div>
</div>


                    <!-- CONTENT -->
                    <div class="p-6 space-y-8">

                        <!-- STATS -->
                        <div class="grid grid-cols-4 gap-4">
                            @foreach([
                                ['📅','1,284','Bookings','+12%'],
                                ['👥','4,823','Clients','+8%'],
                                ['💳','$48.5K','Revenue','+23%'],
                                ['📈','94%','Growth','+5%'],
                            ] as [$icon,$value,$label,$change])
                                <div class="bg-[hsl(210,20%,96%)]
                                            rounded-2xl p-4
                                            shadow-[0_8px_20px_rgba(0,0,0,0.04)]">
                                    <div class="text-xl mb-1">{{ $icon }}</div>
                                    <p class="text-2xl font-bold text-foreground">{{ $value }}</p>
                                    <p class="text-sm text-muted-foreground">{{ $label }}</p>
                                    <p class="text-sm text-[hsl(262,83%,58%)]">{{ $change }}</p>
                                </div>
                            @endforeach
                        </div>

                        <!-- CHART -->
                        <div class="bg-[hsl(210,20%,96%)] rounded-2xl p-4">
                            <div class="flex items-end justify-between h-36">
                                @foreach([30,55,40,70,50,80,65,90,60,85,70] as $v)
                                    <div class="flex-1 max-w-[28px] h-full relative">
                                        <div class="absolute bottom-0 w-full
                                                    bg-[hsl(210,20%,90%)]
                                                    rounded-t-xl"
                                             style="height: {{ $v + 20 }}px"></div>
                                        <div class="absolute bottom-0 w-full
            rounded-t-xl
            bg-gradient-to-t from-[hsl(262,83%,58%)] to-[hsl(330,81%,60%)]"
     style="height: {{ $v }}px"></div>

                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <!-- TODAY SCHEDULE -->
                        <div class="space-y-3">
                            <h4 class="font-semibold text-foreground">
                                Today's Schedule
                            </h4>

                            @foreach([
                                ['9:00 AM','Sarah Johnson','Facial Treatment'],
                                ['10:30 AM','Mike Chen','Massage Therapy'],
                                ['2:00 PM','Emma Wilson','Styling'],
                            ] as [$time,$name,$service])
                                <div class="bg-[hsl(210,20%,96%)]
                                            rounded-2xl px-5 py-4
                                            flex justify-between items-center
                                            shadow-[0_6px_18px_rgba(0,0,0,0.04)]">
                                    <div>
                                        <p class="text-sm font-medium text-[hsl(262,83%,58%)]">
                                            {{ $time }}
                                        </p>
                                        <p class="font-medium text-foreground">{{ $name }}</p>
                                        <p class="text-sm text-muted-foreground">{{ $service }}</p>
                                    </div>
                                    <span class="w-2.5 h-2.5
                                                 bg-[hsl(262,83%,58%)]
                                                 rounded-full"></span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- FLOATING: NEW BOOKING -->
                <div class="absolute -top-6 -right-6 lg:top-10 lg:-right-10 z-20">
                    <div class="bg-white rounded-2xl p-4
                                shadow-[0_25px_60px_rgba(0,0,0,0.15)]
                                flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full
                                    bg-[hsl(166,72%,38%,0.15)]
                                    flex items-center justify-center">
                            ✓
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-foreground">
                                New Booking!
                            </p>
                            <p class="text-xs text-muted-foreground">
                                Just now
                            </p>
                        </div>
                    </div>
                </div>

                <!-- FLOATING: REVENUE -->
                <div class="absolute -bottom-6 -left-6 lg:bottom-10 lg:-left-10 z-20">
                    <div class="bg-white rounded-2xl p-4
                                shadow-[0_25px_60px_rgba(0,0,0,0.15)]
                                flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full
                                    bg-[hsl(166,72%,38%,0.15)]
                                    flex items-center justify-center text-lg">
                            💰
                        </div>
                        <div>
                            <p class="text-sm font-bold text-[hsl(166,72%,38%)]">
                                +$1,250
                            </p>
                            <p class="text-xs text-muted-foreground">
                                Today's revenue
                            </p>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</section>
