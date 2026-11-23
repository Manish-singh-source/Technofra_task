<?php

namespace App\Services;

use App\Models\SocialFestivalEvent;

class FestivalCalendarService
{
    public function seedYear(int $year): int
    {
        // The next-year calendar is maintained as a curated India + international
        // campaign calendar. Lunar dates are noted as expected dates because local
        // moon-sighting and regional calendars can change them.
        $events = [
            ['01-01', "New Year's Day", 'National / Festival', 'India', 'New Year campaign opportunity.'],
            ['01-01', "New Year's Day", 'International / Festival', 'International', 'Global New Year observance.'],
            ['01-13', 'Lohri', 'Indian / Regional', 'India', 'Punjabi winter harvest festival.'],
            ['01-14', 'Makar Sankranti / Pongal', 'Indian / Regional', 'India', 'Harvest festival observed across India.'],
            ['01-26', 'Republic Day', 'National', 'India', 'Indian national holiday.'],
            ['02-06', 'Vasant Panchami / Saraswati Puja', 'Indian / Hindu', 'India', 'Festival of learning and spring.'],
            ['02-14', "Valentine's Day", 'International / Commercial', 'International', 'Global relationship and gifting campaign date.'],
            ['03-08', "International Women's Day", 'International / UN', 'International', 'Global equality and women’s empowerment observance.'],
            ['03-10', 'Eid al-Fitr', 'Indian / Muslim', 'India', 'Expected date; moon sighting may shift the date.'],
            ['03-17', 'Ram Navami', 'Indian / Hindu', 'India', 'Expected date; lunar calendar observance.'],
            ['03-23', 'Holi', 'Indian / Hindu', 'India', 'Festival of colours.'],
            ['03-26', 'Good Friday', 'International / Christian', 'International', 'Christian observance.'],
            ['03-28', 'Easter Sunday', 'International / Christian', 'International', 'Christian festival.'],
            ['04-07', 'Ugadi / Gudi Padwa / Chaitra Navratri', 'Indian / Regional', 'India', 'Regional New Year festivals.'],
            ['04-14', 'Vaisakhi / Vishu / Tamil New Year', 'Indian / Regional', 'India', 'Regional harvest and New Year observances.'],
            ['04-22', 'Earth Day', 'International / Environmental', 'International', 'Environmental awareness campaign date.'],
            ['05-01', 'International Workers’ Day / Labour Day', 'International / Observance', 'International', 'Workers’ rights and labour observance.'],
            ['05-09', "Mother's Day", 'International / Commercial', 'International', 'Common second-Sunday-of-May observance.'],
            ['05-17', 'Eid al-Adha / Bakrid', 'Indian / Muslim', 'India', 'Expected date; moon sighting may shift the date.'],
            ['05-20', 'Buddha Purnima', 'Indian / Buddhist', 'India', 'Buddhist festival; date may vary by tradition.'],
            ['06-05', 'World Environment Day', 'International / UN', 'International', 'United Nations environmental observance.'],
            ['06-20', "Father's Day", 'International / Commercial', 'International', 'Common third-Sunday-of-June observance.'],
            ['06-21', 'International Day of Yoga', 'International / India', 'India', 'United Nations observance with major Indian campaigns.'],
            ['07-05', 'Rath Yatra', 'Indian / Hindu', 'India', 'Chariot festival, especially associated with Puri.'],
            ['07-30', 'International Day of Friendship', 'International / UN', 'International', 'United Nations friendship observance.'],
            ['08-12', 'International Youth Day', 'International / UN', 'International', 'United Nations youth observance.'],
            ['08-15', 'Independence Day', 'National', 'India', 'Indian national holiday.'],
            ['08-15', 'Milad-un-Nabi', 'Indian / Muslim', 'India', 'Expected date; moon sighting may shift the date.'],
            ['08-17', 'Raksha Bandhan', 'Indian / Hindu', 'India', 'Sibling celebration.'],
            ['08-25', 'Janmashtami', 'Indian / Hindu', 'India', 'Birth anniversary of Lord Krishna.'],
            ['09-04', 'Ganesh Chaturthi', 'Indian / Hindu', 'India', 'Festival of Lord Ganesha.'],
            ['09-21', 'International Day of Peace', 'International / UN', 'International', 'United Nations peace observance.'],
            ['10-02', 'Mahatma Gandhi Jayanti', 'National', 'India', 'Indian national observance.'],
            ['10-07', 'Maha Ashtami', 'Indian / Hindu', 'India', 'Durga Puja/Navratri observance.'],
            ['10-09', 'Dussehra / Vijayadashami', 'Indian / Hindu', 'India', 'Victory of good over evil.'],
            ['10-18', 'Karwa Chauth', 'Indian / Hindu', 'India', 'Married women’s fasting and family observance.'],
            ['10-28', 'Naraka Chaturdashi', 'Indian / Hindu', 'India', 'Diwali festival period.'],
            ['10-29', 'Diwali / Deepavali / Lakshmi Puja', 'Indian / Hindu / Jain / Sikh', 'India', 'Festival of lights; date is moon-calendar sensitive.'],
            ['10-30', 'Govardhan Puja', 'Indian / Hindu', 'India', 'Day after Diwali.'],
            ['10-31', 'Bhai Dooj', 'Indian / Hindu', 'India', 'Sibling celebration after Diwali.'],
            ['10-31', 'Halloween', 'International / Cultural', 'International', 'Global cultural and commercial campaign date.'],
            ['11-04', 'Chhath Puja', 'Indian / Hindu / Regional', 'India', 'Sun worship festival.'],
            ['11-14', 'Guru Nanak Jayanti', 'Indian / Sikh', 'India', 'Birth anniversary of Guru Nanak Dev Ji.'],
            ['11-25', 'Thanksgiving Day', 'International / Cultural', 'International', 'International campaign calendar entry; local observance varies.'],
            ['12-05', 'Hanukkah Begins', 'International / Jewish', 'International', 'Jewish festival; begins at sunset.'],
            ['12-24', 'Christmas Eve', 'International / Christian', 'International', 'Christmas campaign and observance date.'],
            ['12-25', 'Christmas Day', 'International / Christian', 'International', 'Global Christian festival.'],
            ['12-31', "New Year's Eve", 'International / Festival', 'International', 'Year-end campaign and celebration date.'],
        ];

        // The curated dates above are reused as the campaign calendar template
        // for whichever year the administrator selects. Year-sensitive dates
        // can still be edited afterwards from Festival Master.

        // Lunar and movable dates must not be copied from another year. These
        // overrides are maintained for the currently supported Indian festival
        // calendars; fixed-date international observances remain unchanged.
        $dateOverrides = match ($year) {
            2026 => [
                'Vasant Panchami / Saraswati Puja' => '01-23',
                'Holi' => '03-04',
                'Eid al-Fitr' => '03-21',
                'Ram Navami' => '03-26',
                'Ugadi / Gudi Padwa / Chaitra Navratri' => '03-19',
                'Good Friday' => '04-03',
                'Easter Sunday' => '04-05',
                'Eid al-Adha / Bakrid' => '05-28',
                'Buddha Purnima' => '05-01',
                'Rath Yatra' => '07-16',
                'Raksha Bandhan' => '08-28',
                'Janmashtami' => '09-04',
                'Ganesh Chaturthi' => '09-14',
                'Milad-un-Nabi' => '08-26',
                'Maha Ashtami' => '10-19',
                'Dussehra / Vijayadashami' => '10-20',
                'Karwa Chauth' => '10-29',
                'Naraka Chaturdashi' => '11-08',
                'Diwali / Deepavali / Lakshmi Puja' => '11-08',
                'Govardhan Puja' => '11-09',
                'Bhai Dooj' => '11-11',
                'Chhath Puja' => '11-15',
                'Guru Nanak Jayanti' => '11-24',
                'Thanksgiving Day' => '11-26',
                'Hanukkah Begins' => '12-05',
            ],
            2027 => [
                'Vasant Panchami / Saraswati Puja' => '02-11',
                'Holi' => '03-22',
                'Ram Navami' => '04-15',
                'Good Friday' => '03-26',
                'Easter Sunday' => '03-28',
                'Eid al-Adha / Bakrid' => '05-17',
                'Buddha Purnima' => '05-20',
                'Rath Yatra' => '07-05',
                'Raksha Bandhan' => '08-17',
                'Janmashtami' => '08-25',
                'Ganesh Chaturthi' => '09-04',
                'Maha Ashtami' => '10-07',
                'Dussehra / Vijayadashami' => '10-09',
                'Karwa Chauth' => '10-18',
                'Naraka Chaturdashi' => '10-28',
                'Diwali / Deepavali / Lakshmi Puja' => '10-29',
                'Govardhan Puja' => '10-30',
                'Bhai Dooj' => '10-31',
                'Chhath Puja' => '11-04',
                'Guru Nanak Jayanti' => '11-14',
                'Thanksgiving Day' => '11-25',
                'Hanukkah Begins' => '12-25',
            ],
            default => [],
        };

        foreach ($events as [$monthDay, $name, $category, $region, $description]) {
            $monthDay = $dateOverrides[$name] ?? $monthDay;
            $date = sprintf('%04d-%s', $year, $monthDay);
            $festival = SocialFestivalEvent::query()
                ->where('name', $name)
                ->where('country_region', $region)
                ->whereYear('event_date', $year)
                ->first() ?? new SocialFestivalEvent();
            $festival->fill([
                'name' => $name,
                'event_date' => $date,
                'country_region' => $region,
                'category' => $category,
                'recurrence_type' => 'yearly',
                'client_id' => null,
                'description' => $description . " Calendar year: {$year}.",
                'reference_url' => null,
            ])->save();
        }

        return count($events);
    }

    public function seedNextYear(int $year): int
    {
        return $this->seedYear($year);
    }
}
