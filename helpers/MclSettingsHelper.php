<?php

class MclSettingsHelper {

    const defaultStatisticsMclNumber = true;
    const defaultStatisticsNumberOfDays = 30;
    const defaultStatisticsGoogleChartsDailyOptions = "annotations: { textStyle: { color: '#000000', fontSize: 9, bold:true }, highContrast: true, alwaysOutside: true},
height: data.getNumberOfRows() * 15 + 100,
legend: { position: 'top', maxLines: 4, alignment: 'center' },
bar: { groupWidth: '70%' },
focusTarget: 'category',
chartArea:{left: 100, top: 80, width: '75%', height: data.getNumberOfRows() * 15},
isStacked: true,";
    const defaultStatisticsNumberOfMonths = 6;
    const defaultStatisticsGoogleChartsMonthlyOptions = "annotations: { textStyle: { color: '#000000', fontSize: 9, bold:true }, highContrast: true, alwaysOutside: true},
height: data.getNumberOfRows() * 15 + 100,
legend: { position: 'top', maxLines: 4, alignment: 'center' },
bar: { groupWidth: '70%' },
focusTarget: 'category',
chartArea:{left: 60, top: 80, width: '75%', height: data.getNumberOfRows() * 15},
isStacked: true,";
    const defaultOtherCommaInTags = true;
    const defaultOtherSeparator = "-";

    static function defaultStatisticsDailyDateFormat() {
        return __( 'Y-m-j', 'media-consumption-log' );
    }

    static function defaultStatisticsMonthlyDateFormat() {
        return __( 'Y-m', 'media-consumption-log' );
    }

    static function defaultOtherMclNumberAnd() {
        return __( 'and', 'media-consumption-log' );
    }

    static function defaultOtherMclNumberTo() {
        return __( 'to', 'media-consumption-log' );
    }

    static function getStatusExcludeCategory() {
        return get_option( 'mcl_settings_status_exclude_category' );
    }

    static function getStatisticsExcludeCategory() {
        return get_option( 'mcl_settings_statistics_exclude_category' );
    }

    static function isStatisticsMclNumber() {
        $value = get_option( 'mcl_settings_statistics_mcl_number', self::defaultStatisticsMclNumber );

        if ( empty( $value ) ) {
            return false;
        } else {
            return true;
        }
    }

    static function getStatisticsNumberOfDays() {
        $value = get_option( 'mcl_settings_statistics_number_of_days' );

        if ( empty( $value ) ) {
            return self::defaultStatisticsNumberOfDays;
        } else {
            return $value;
        }
    }

    static function getStatisticsDailyDateFormat() {
        $value = get_option( 'mcl_settings_statistics_daily_date_format' );

        if ( empty( $value ) ) {
            return self::defaultStatisticsDailyDateFormat();
        } else {
            return $value;
        }
    }

    static function getStatisticsGoogleChartsDailyOptions() {
        $value = get_option( 'mcl_settings_statistics_google_charts_daily_options' );

        if ( empty( $value ) ) {
            return self::defaultStatisticsGoogleChartsDailyOptions;
        } else {
            return $value;
        }
    }

    static function getStatisticsNumberOfMonths() {
        $value = get_option( 'mcl_settings_statistics_number_of_months' );

        if ( empty( $value ) ) {
            return self::defaultStatisticsNumberOfMonths;
        } else {
            return $value;
        }
    }

    static function getStatisticsMonthlyDateFormat() {
        $value = get_option( 'mcl_settings_statistics_monthly_date_format' );

        if ( empty( $value ) ) {
            return self::defaultStatisticsMonthlyDateFormat();
        } else {
            return $value;
        }
    }

    static function getStatisticsGoogleChartsMonthlyOptions() {
        $value = get_option( 'mcl_settings_statistics_google_charts_monthly_options' );

        if ( empty( $value ) ) {
            return self::defaultStatisticsGoogleChartsMonthlyOptions;
        } else {
            return $value;
        }
    }

    static function isOtherCommaInTags() {
        $value = get_option( 'mcl_settings_other_comma_in_tags', self::defaultOtherCommaInTags );

        if ( empty( $value ) ) {
            return false;
        } else {
            return true;
        }
    }

    static function getOtherSeprator() {
        $value = get_option( 'mcl_settings_other_separator' );

        if ( empty( $value ) ) {
            return self::defaultOtherSeparator;
        } else {
            return $value;
        }
    }

    static function getOtherMclNumberAnd() {
        $value = get_option( 'mcl_settings_other_mcl_number_and' );

        if ( empty( $value ) ) {
            return self::defaultOtherMclNumberAnd();
        } else {
            return $value;
        }
    }

    static function getOtherMclNumberTo() {
        $value = get_option( 'mcl_settings_other_mcl_number_to' );

        if ( empty( $value ) ) {
            return self::defaultOtherMclNumberTo();
        } else {
            return $value;
        }
    }

}

?>