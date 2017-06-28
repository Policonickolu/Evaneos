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

            // ----- AJOUTER LES NOUVELLES BALISES A REMPLACER ICI -----

            $replacement = array();

            if(strpos($text, '[quote:destination_name]'))
                $replacement['[quote:destination_name]'] = $destination->countryName;

            if(strpos($text, '[quote:destination_link]'))
                $replacement['[quote:destination_link]'] = $site->url . '/' . $destination->countryName . '/quote/' . $quoteFromRepo->id;

            if(strpos($text, '[quote:summary_html]'))
                $replacement['[quote:summary_html]'] = Quote::renderHtml($quoteFromRepo);

            if(strpos($text, '[quote:summary]'))
                $replacement['[quote:summary]'] = Quote::renderText($quoteFromRepo);


            /* Ne pas faire de strpos pourrait être plus avantageux si les chances que les balises ne se trouvent pas dans le template sont faibles.

            $replacement = array ()
                '[quote:destination_name]'  => $destination->countryName,
                '[quote:destination_link]'  => $site->url . '/' . $destination->countryName . '/quote/' . $quoteFromRepo->id,
                '[quote:summary_html]'      => Quote::renderHtml($quoteFromRepo),
                '[quote:summary]'           => Quote::renderText($quoteFromRepo)
            );

            */

            $text = str_replace(array_keys($replacement), $replacement, $text);

        }

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
