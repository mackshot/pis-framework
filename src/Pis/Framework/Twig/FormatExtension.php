<?php

namespace Pis\Framework\Twig;

use Twig\Extension\AbstractExtension;

if (!defined('ENT_SUBSTITUTE')) {
    // use 0 as hhvm does not support several flags yet
    define('ENT_SUBSTITUTE', 0);
}

class FormatExtension extends AbstractExtension
{
    protected $dateFormats = array('F j, Y', 'F j, Y H:i', '%d days');
    protected $numberFormat = array(0, '.', ',');
    protected $timezone = null;
    protected $escapers = array();

    /**
     * Sets the default format to be used by the date filter.
     *
     * @param string $dateFormat             The default date format string
     * @param string $dateTimeFormat
     * @param string $dateIntervalFormat The default date interval format string
     */
    public function setDateFormat($dateFormat = null, $dateTimeFormat = null, $dateIntervalFormat = null)
    {
        if (null !== $dateFormat) {
            $this->dateFormats[0] = $dateFormat;
        }

        if (null !== $dateTimeFormat) {
            $this->dateFormats[1] = $dateTimeFormat;
        }

        if (null !== $dateIntervalFormat) {
            $this->dateFormats[2] = $dateIntervalFormat;
        }
    }

    /**
     * Gets the default format to be used by the date filter.
     *
     * @return array The default date format string and the default date interval format string
     */
    public function getDateFormat()
    {
        return $this->dateFormats;
    }

    /**
     * Sets the default timezone to be used by the date filter.
     *
     * @param \DateTimeZone|string $timezone The default timezone string or a DateTimeZone object
     */
    public function setTimezone($timezone)
    {
        $this->timezone = $timezone instanceof \DateTimeZone ? $timezone : new \DateTimeZone($timezone);
    }

    /**
     * Gets the default timezone to be used by the date filter.
     *
     * @return \DateTimeZone The default timezone currently in use
     */
    public function getTimezone()
    {
        if (null === $this->timezone) {
            $this->timezone = new \DateTimeZone(date_default_timezone_get());
        }

        return $this->timezone;
    }

    /**
     * Sets the default format to be used by the number_format filter.
     *
     * @param integer $decimal      The number of decimal places to use.
     * @param string  $decimalPoint The character(s) to use for the decimal point.
     * @param string  $thousandSep  The character(s) to use for the thousands separator.
     */
    public function setNumberFormat($decimal, $decimalPoint, $thousandSep)
    {
        $this->numberFormat = array($decimal, $decimalPoint, $thousandSep);
    }

    /**
     * Get the default format used by the number_format filter.
     *
     * @return array The arguments for number_format()
     */
    public function getNumberFormat()
    {
        return $this->numberFormat;
    }

    /**
     * Returns the token parser instance to add to the existing list.
     *
     * @return \Twig_TokenParser[] An array of Twig_TokenParser instances
     */
    public function getTokenParsers()
    {
        return array(
        );
    }

    /**
     * Returns a list of filters to add to the existing list.
     *
     * @return array An array of filters
     */
    public function getFilters()
    {
        return array(
            new \Twig\TwigFilter('datetime', function(\Twig_Environment $env, $date, $format = null, $timezone = null)
                {
                    $formats = $env->getExtension('format')->getDateFormat();
                    $format = $formats[1];

                    return twig_date_converter($env, $date, $timezone)->format($format);
                },
                array('needs_environment' => true)
            ),
        );
    }

    /**
     * Returns a list of global functions to add to the existing list.
     *
     * @return array An array of global functions
     */
    public function getFunctions()
    {
        return array(
        );
    }

    /**
     * Returns a list of tests to add to the existing list.
     *
     * @return array An array of tests
     */
    public function getTests()
    {
        return array(
        );
    }

    /**
     * Returns a list of operators to add to the existing list.
     *
     * @return array An array of operators
     */
    public function getOperators()
    {
        return array(
        );
    }

    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName()
    {
        return 'format';
    }

    /**
     * Converts a date to the given format.
     *
     * <pre>
     *   {{ post.published_at|date("m/d/Y") }}
     * </pre>
     *
     * @param \Twig_Environment             $env      A Twig_Environment instance
     * @param \DateTime|string $date     A date
     * @param string                       $format   A format
     * @param \DateTimeZone|string          $timezone A timezone
     *
     * @return string The formatted date
     */

}