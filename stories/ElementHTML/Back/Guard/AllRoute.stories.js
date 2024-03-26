import TwigComponent from '@components/elementHTML/back/guard/allroute.html.twig';
import '@/back.scss';
export default {
  title: 'ElementHTML/Back/Guard',
  argTypes: {
    content: { control: 'text' },
    size: {
      options: [ 'sm', 'md', 'lg' ],
      control: 'select',
    },
    type: {
      options: [ 'primary', 'gray', 'white' ],
      control: 'select',
    },
    isPlain: { control: 'boolean' },
    isStretched: { control: 'boolean' },
    iconName: {
      options: [ 'star', 'burger-menu' ],
      control: 'select',
    },
    iconPosition: {
      options: [ 'left', 'right' ],
      control: 'radio',
    },
  },
};

const Template = (args) => {
  return TwigComponent({
    button_content: args.content,
    button_size: args.size,
    button_type: args.type,
    button_isPlain: args.isPlain,
    button_isStretched: args.isStretched,
    button_iconName: args.iconName,
    button_iconPosition: args.iconPosition,
  });
};

export const AllRoute = Template.bind({});
AllRoute.args = {
  content: 'Voir le lieu',
  size: 'md',
  type: 'primary',
  isPlain: false,
  isStretched: false,
  iconName: '',
  iconPosition: 'left',
};
