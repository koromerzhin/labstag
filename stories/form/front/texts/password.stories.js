import TwigComponent from '@components/form/front/texts/password.html.twig';
import '@/front.scss';
export default {
  title: 'form/front/Texts',
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

export const Password = Template.bind({});
Password.args = {
  content: 'Voir le lieu',
  size: 'md',
  type: 'primary',
  isPlain: false,
  isStretched: false,
  iconName: '',
  iconPosition: 'left',
};
