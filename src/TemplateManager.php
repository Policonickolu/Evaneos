<?php

class TemplateManager
{
    public function getTemplateComputed(Template $tpl, array $data)
    {
        if (!$tpl) {
            throw new \RuntimeException('no tpl given');
        }

        $textReplaced = clone($tpl);
        $textReplaced->subject = $this->computeText($textReplaced->subject, $data);
        $textReplaced->content = $this->computeText($textReplaced->content, $data);

        return $textReplaced;
    }

    private function computeText($text, array $data)
    {
        $APPLICATION_CONTEXT = ApplicationContext::getInstance();

        $quote = (isset($data['quote']) and $data['quote'] instanceof Quote) ? $data['quote'] : null;

        if ($quote)
        {
            $quoteFromRepo  = QuoteRepository::getInstance()->getById($quote->id);
            $site           = SiteRepository::getInstance()->getById($quote->siteId);
            $destination    = DestinationRepository::getInstance()->getById($quote->destinationId);

            if(strpos($text, '[quote:destination_link]') !== false){
                $destination = DestinationRepository::getInstance()->getById($quote->destinationId);
            }

            $summaryHtml = strpos($text, '[quote:summary_html]');
            $summary     = strpos($text, '[quote:summary]');

            if ($summaryHtml !== false || $summary !== false) {
                if ($summaryHtml !== false) {
                    $text = str_replace(
                        '[quote:summary_html]',
                        Quote::renderHtml($quoteFromRepo),
                        $text
                    );
                }
                if ($summary !== false) {
                    $text = str_replace(
                        '[quote:summary]',
                        Quote::renderText($quoteFromRepo),
                        $text
                    );
                }
            }

            (strpos($text, '[quote:destination_name]') !== false) and $text = str_replace('[quote:destination_name]', $destination->countryName, $text);
        }

        if (isset($destination))
            $text = str_replace('[quote:destination_link]', $site->url . '/' . $destination->countryName . '/quote/' . $quoteFromRepo->id, $text);
        else
            $text = str_replace('[quote:destination_link]', '', $text);

        /*
         * USER
         * [user:*]
         */
        $user  = (isset($data['user'])  and ($data['user']  instanceof User))  ? $data['user']  : $APPLICATION_CONTEXT->getCurrentUser();
        if($user) {
            (strpos($text, '[user:first_name]') !== false) and $text = str_replace('[user:first_name]', ucfirst(mb_strtolower($user->firstname)), $text);
        }

        return $text;
    }

}
