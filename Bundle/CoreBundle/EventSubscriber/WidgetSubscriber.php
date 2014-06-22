<?php
namespace Victoire\Bundle\CoreBundle\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Victoire\Bundle\CoreBundle\Event\WidgetQueryEvent;
use Victoire\Bundle\CoreBundle\VictoireCmsEvents;
use Victoire\Bundle\CoreBundle\Event\WidgetBuildFormEvent;
use Victoire\Bundle\CoreBundle\Theme\ThemeWidgetInterface;

class WidgetSubscriber implements EventSubscriberInterface
{

    private $container;

    public function __construct($container)
    {
        $this->container = $container;
    }

    /**
     * {@inheritDoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            // VictoireCmsEvents::WIDGET_PRE_RENDER => array(
            // ),
            // VictoireCmsEvents::WIDGET_POST_RENDER => array(
            // ),
            VictoireCmsEvents::WIDGET_POST_QUERY => array(
                array('buildFilterQuery'),
            ),
            VictoireCmsEvents::WIDGET_BUILD_FORM => array(
                array('addThemeField'),
                array('addQueryMode'),
            ),
        );
    }

    public function buildFilterQuery(WidgetQueryEvent $event)
    {
        $qb = $event->getQb();
        // add this fake condition to ensure that there is always a "where" clause.
        // In query mode, usage of "AND" will be alwayse valid instead of "WHERE"
        $qb->andWhere('1 = 1');
        if ($this->container->has('victoire_core.filter_chain')) {

            $request = $event->getRequest();
            $widget = $event->getWidget();
            $filters = $request->query->get('filter');
            $listId = $filters['listing'];

            if ($listId == $widget->getId()) {
                unset($filters['listing']);
                foreach ($this->container->get('victoire_core.filter_chain')->getFilters() as $name => $filter) {
                    if (!empty($filters[$name])) {
                        $filter->buildQuery($qb, $filters[$name]);
                        $widget->filters[$name] = $filter->getFilters($filters[$name]);

                    }
                }

            }
        }
    }

    public function addThemeField(WidgetBuildFormEvent $event)
    {
        $form = $event->getForm();
        $widget = $event->getWidget();
        // if we are in edition mode, do not add theme field
        if ($widget->getId()) {
            return;
        }
        $manager = $this->container->get('widget_manager')->getManager($widget);
        if ($widget instanceof ThemeWidgetInterface) {
            $widgetClass = get_parent_class($widget);
            $currentWidget = $manager->getName();
        } else {
            $widgetClass = get_class($widget);
            $currentWidget = $this->container->get('widget_manager')->getWidgetType($widget);
        }
        $widgets = $this->container->getParameter('victoire_core.widgets');
        foreach ($this->container->getParameter('victoire_core.widgets') as $widgetName => $params) {
            if ($params['class'] === $widgetClass) {
                break;
            }
        }
        $themeChain = $this->container->get('victoire_core.theme_chain');
        $themeObjs = $themeChain->getThemes($widgetClass);
        if (count($themeObjs) > 0) {

            $themes = array($widgetName => 'widget.form.theme.default');
            foreach ($themeObjs as $themeObj) {
                $themes[$themeObj->getName()] = 'widget.form.theme.' . $themeObj->getName();
            }

            $form->add('theme', 'choice',
                array(
                    'mapped' => false,
                    'label' => 'widget.form.theme.label',
                    'choices' => $themes,
                    'data' => $currentWidget,
                    'attr' => array('class' => 'theme-choices'),
                )
            );
        }
    }
    /**
     * Activates the query behavior adding a "query" text field in the form.
     * This field is appended only if:
     *  - current user is a Victoire Developer
     *  - the form is in "entity" mode (it have a "fields" field)
     *
     * @param WidgetBuildFormEvent $event
     */
    public function addQueryMode(WidgetBuildFormEvent $event)
    {
        $form = $event->getForm();
        $security = $this->container->get('security.context');
        if ($form->has('fields') && $security->isGranted('ROLE_VICTOIRE_DEVELOPER')) {
            $form->add('query');
        }

    }

}